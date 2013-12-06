<?php namespace Noherczeg\RestExt\Services;

use Illuminate\Support\Facades\Lang;

class CSVConverter {

    protected $data;

    /**
     * @param $columns
     */
    public function __construct($columns) {
        $this->data = '"' . implode('","', $columns) . '"' . PHP_EOL;
    }

    /**
     * @param $row
     */
    public function addRow($row) {
        $this->data .= '"' . implode('","', $this->replaceBooleans($row)) . '"' . PHP_EOL;
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->data;
    }

    /**
     * Replaces the given columns with a translated string value if any of them is of type boolean.
     *
     * @param array $row
     * @return array
     */
    private function replaceBooleans(array $row)
    {
        $replaced = null;

        foreach ($row as $key => $value) {
            if (is_bool($value)) {
                if ($value)
                    $replaced[$key] = Lang::get('general.yes');
                else
                    $replaced[$key] = Lang::get('general.no');
            } else {
                $replaced[$key] = $value;
            }
        }

        return $replaced;
    }

} 