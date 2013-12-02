Starter Guide
=======

This is a guide on using the provided components and extra files to build an API.

Please use namespaces everywhere just like I do, if for nothing else, but to prevent naming collisions!

For simplicity's sake I'll stick to the general layout of the app structure which somes with Laravel, but you are of course
free to re-organize your app as you wish.


#Application structure:

The core of our apps are:
+ Models and
+ Repositories (plus services if needed)

On top of these we'll have:
+ routing
+ controllers and
+ additional services such as security


##Models:

As stated in the introduction when working with this package it's advised to either implement _Noherczeg\RestExt\Entities\ResourceEntity_
or extend _Noherczeg\RestExt\Entities\ResourceEloquentEntity_ to gain all of the features it can provide.

The provided parent Model is an extension of Laravel's own model so anything that you would do with those, you'll able
to do with `ResourceEloquentEntity` as well for example.

```
<?php

// app/models/Post.php

use Noherczeg\RestExt\Entities\ResourceEloquentEntity;
use Noherczeg\RestExt\Entities\ResourceEntity;

class Post extends ResourceEloquentEntity implements ResourceEntity {

    protected $fillable = ['title', 'content'];

    protected $rules = [
        'title' => 'required|alpha_num|between:6,128',
        'kod' => 'required'
    ];

    public function comments()
    {
        return $this->belongsToMany('Comment', 'post_has_comment', 'post_id', 'comment_id');
    }

}
```

```
<?php

// app/models/Comment.php

use Noherczeg\RestExt\Entities\ResourceEloquentEntity;
use Noherczeg\RestExt\Entities\ResourceEntity;

class Comment extends ResourceEloquentEntity implements ResourceEntity {

    protected $fillable = ['email', 'comment'];

    protected $rules = [
        'email' => 'required|email',
        'comment' => 'required|alpha_num'
    ];

    public function posts()
    {
        return $this->belongsToMany('Post', 'post_has_comment', 'comment_id', 'post_id');
    }

}
```

##Repositories:

Using Repositories lets you solve complex tasks, and makes your code testable, so it's advised to use them, even
is you ignore services.

I tend to create a namespace for my repositories, If you do so as well, you shouldn't forget to register it with composer
via putting them in the `composer.json` file in your root directory:

```
"autoload": {
    "classmap": [
        ...
        "app/Entities"
}
```

_Sample interface:_

```
<?php

// app/repositories/PostRepository.php

use Noherczeg\RestExt\Repository\CRUDRepository;

interface PostRepository extends CRUDRepository {

    // no extra code is required for basic CRUD-ish usage

}
```

_Sample implementation:_

```
<?php

namespace Repositories;

use Repositories\PostRepository;
use Noherczeg\RestExt\Repository\RestExtRepository;

class PostEloquentRepository extends RestExtRepository implements PostRepository {

    // we inject the Model which is managed by this Repository (check the parent class for functionallity)
    public function __construct(Post $postEntity)
    {
        $this->entity = $postEntity;
    }
}
```

##Services:

I'll provide one service which will be an implementation of the authorization service interface which comes with the package.
This serves as a data provider which lets controllers know who should have access to them and who should be denied.

You may use Sentry, or Entrust, or your services in this implementation of course :)

```
<?php

// app/services/AuthServiceImpl.php

namespace Services;


use Noherczeg\RestExt\Services\AuthorizationService;

class AuthServiceImpl implements AuthorizationService {

    /** @var array An array of simple role names which are associated with the currently logged in user  */
    private $roles = [];

    private $rolesFieldName = 'role_name';

    public function __construct()
    {
        // get the roles for the current user, this is a basic Eloquent call as you can see
        $rolesTmp = \Auth::user()->with('roles')->where('id', \Auth::user()->id)->firstOrFail()->getRelation('roles')->toArray();

        foreach ($rolesTmp as $role) {
            $this->roles[] = $role[$this->rolesFieldName];
        }
    }

    /**
     * Checks if the authenticated User has any of the given Roles (names) specified in the parameter array
     *
     * @param array $roles Array of strings
     * @return boolean
     */
    public function hasRoles(array $roles)
    {
        foreach ($roles as $role) {
            if (in_array($role, $this->roles))
                return true;
        }

        return false;
    }

    /**
     * Checks if the authenticated User has the given Role or not
     *
     * @param string $role
     * @return boolean
     */
    public function hasRole($role)
    {
        if (in_array($role, $this->roles))
            return true;
        return false;
    }
}
```

##Controllers:

Controllers serve as connectors between our routes and the service layer which operates with the underlying data.

```
<?php

use Repositories\CommentRepository;
use Noherczeg\RestExt\Controllers\RestExtController;
use Noherczeg\RestExt\Services\AuthorizationService;

class CommentsController extends RestExtController {

    private $commentRepository;

    public function __construct(CommentRepository $commentRepository, AuthorizationService $auth)
    {
        parent::__construct();
        $this->commentRepository = $commentRepository;
        $this->authorizationService = $auth;
    }

    // this method will provide all of our posts, cosider it as a list/collection
    public function index()
    {
        // We check if the "page" parameter has been set in the query string or not, and if it does, we set the
        // repository accordingly which means it will provide a Paginator for us, and not a simple Collection.
        //
        // This means we'll have 15 results per page, but if you would like to give users the ability to control
        // how many elements they can get / page, you could use enablePagination($this->pageParam()) since
        // $this->pageParam() returns the parameter's value if it's set.
        if ($this->pageParam())
            $this->commentRepository->enablePagination(15);

        // Then we create our Resource Collection with links as well
        //
        // Links are generated for pages as well, plus as an addition pagination meta info is provided as well
        // e.g.: isLastPage, isFirstPage, etc :)
        //
        // If you want links which point to the individual resources inside the collection, you should call create(true)
        $resource = RestExt::from($this->commentRepository->all())->links()->create();

        // After we have our Resource, we can add more links to it, provided is a link to the "parent" resource which
        // could point to a Post resource for example
        //
        // Check RestLinker for other link generation methods :)
        $resource->addLink(RestLinker::createParentLink());

        // to send the resource to the client we just call sendResource($resource) where $resource is our full result
        return RestResponse::sendResource($resource);
    }

    // this method will show the client one full blown Resource, and not a single collection, with Authorization and links
    // to each individual database reference
    public function show($id)
    {
        // this should be called at the start of each method, the first param can be replaced with "except" for inverted usage
        $this->allowForRoles('only', ['Admin']);

        // we can enable links separately as well, not just chained to a Resource creation
        RestExt::links(true);

        $comment = $this->commentRepository->findById($id);

        $resource = RestExt::from($comment)->create();

        $resource->addLink(RestLinker::createParentLink());

        // With linksToEntityRelations($entity) the Linker will generate links to all the relations that the Entity returned
        // by the Repository has. Which means the ones provided by "with('rel1', 'rel2', 'etc')".
        $resource->addLinks(RestLinker::linksToEntityRelations($comment));

        return $this->sendResource($resource);
    }
}
```

##Routes:

Routes can be designed and used as you wish, an example has been provided in the _extra_ folder of the package, you could
use that as a guideline:

I won't copy [RootController](https://github.com/noherczeg/RestExt/blob/master/extra/RootController.php) here since you can check it out in the extra folder.

```
// app/routes.php

// we'll use versioning in our app, since it's considered as a good practice
Route::get('/', function()
{
    return Redirect::to('/v1', 301);
});

Route::group(array('prefix' => 'v1', 'before' => 'api.auth'), function()
{
    Route::get('/', 'RootController@discover');
    Route::get('posts', 'PostsController@index');
    Route::get('posts/{id}', 'PostsController@show');
    Route::get('comments', 'CommentsController@index');
    Route::get('comments/{id}', 'CommentsController@show');

    // etc...

});
```

##Basic Authentication

Example provided in the [filters.php](https://github.com/noherczeg/RestExt/blob/master/extra/filters.php) file:

```
Route::filter('api.auth', function()
{
    if (!Request::getUser())
    {
        App::abort(401, 'A valid API key is required');
    }

    // we only ask for an API key, no password
    $user = User::where('api_key', '=', Request::getUser())->first();

    if (!$user)
    {
        App::abort(401);
    }

    Auth::login($user);
});
```

# Configuration:

For the currently available configuration please check out the published [config.php](https://github.com/noherczeg/RestExt/blob/master/src/config/config.php)
file and the parent classes which you extend / the interfaces you implement. Detailed Documentary will be provided for
everything when I'll have more time.

# Coming soon:

I'll write more details into this guide, maybe extend it as well, and provide a more complex example as well. This
should let you start off with this package for now. If you'd like to go further with it you should check out the source
code, I've provided detailed comments with each class and method, so it's not impossible to get started with this currently
either.