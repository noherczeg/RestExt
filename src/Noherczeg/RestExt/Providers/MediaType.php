<?php

namespace Noherczeg\RestExt\Providers;

final class MediaType {

    const __default = self::APPLICATION_JSON;

    /** multipurpose files */
    const APPLICATION_JSON = 'application/json';
    const APPLICATION_XML = 'application/xml';
    const APPLICATION_ZIP = 'application/zip';
    const APPLICATION_JAVASCRIPT = 'application/javascript';
    const APPLICATION_PDF = 'application/pdf';
    const APPLICATION_GZIP = 'application/gzip';
    const APPLICATION_ATOM_XML = 'application/atom+xml';
    const APPLICATION_ECMASCRIPT = 'application/ecmascript';
    const APPLICATION_OCTET_STREAM = 'application/octet-stream';
    const APPLICATION_OGG = 'application/ogg';
    const APPLICATION_POSTSCRIPT = 'application/postscript';
    const APPLICATION_RSS_XML = 'application/rss+xml';
    const APPLICATION_SOAP_XML = 'application/soap+xml';
    const APPLICATION_FONT_WOFF = 'application/font-woff';
    const APPLICATION_XHTML = 'application/xhtml+xml';
    const APPLICATION_DTD = 'application/xml-dtd';
    const APPLICATION_FORM_URLENCODED = 'application/x-www-form-urlencoded';

    /** audio */
    const AUDIO_BASIC = 'audio/basic';
    const AUDIO_L24 = 'audio/L24';
    const AUDIO_MP4 = 'audio/mp4';
    const AUDIO_MPEG = 'audio/mpeg';
    const AUDIO_OGG = 'audio/ogg';
    const AUDIO_VORBIS = 'audio/vorbis';
    const AUDIO_REALAUDIO = 'audio/vnd.rn-realaudio';
    const AUDIO_WAVE = 'audio/vnd.wave';
    const AUDIO_WEBM = 'audio/webm';

    /** images */
    const IMAGE_PNG = 'image/png';
    const IMAGE_GIF = 'image/gif';
    const IMAGE_JPEG = 'image/jpeg';
    const IMAGE_PJPEG = 'image/pjpeg';
    const IMAGE_SVG = 'image/svg+xml';
    const IMAGE_TIFF = 'image/tiff';

    /** messages */
    const MESSAGE_HTTP = 'message/http';
    const MESSAGE_IMDN = 'message/imdn+xml';
    const MESSAGE_EMAIL = 'message/partial';
    const MESSAGE_EML = 'message/rfc822';

    /** archives and other objects made of more than one part */
    const MULTIPART_EMAIL_MIXED = 'multipart/mixed';
    const MULTIPART_EMAIL_ALTERNATIVE = 'multipart/alternative';
    const MULTIPART_EMAIL_RELATED = 'multipart/related';
    const MULTIPART_FORM_DATA = 'multipart/form-data';
    const MULTIPART_SIGNED = 'multipart/signed';
    const MULTIPART_ENCRYPTED = 'multipart/encrypted';

    /** human-readable text */
    const TEXT_HTML = 'text/html';
    const TEXT_PLAIN = 'text/plain';
    const TEXT_CMD = 'text/cmd';
    const TEXT_CSS = 'text/css';
    const TEXT_CSV = 'text/csv';
    const TEXT_VCARD = 'text/vcard';
    const TEXT_XML = 'text/xml';

    /** video */
    const VIDEO_MPEG = 'video/mpeg';
    const VIDEO_MP4 = 'video/mp4';
    const VIDEO_OGG = 'video/ogg';
    const VIDEO_WEBM = 'video/webm';
    const VIDEO_QUICKTIME = 'video/quicktime';
    const VIDEO_MATROSKA = 'video/x-matroska';
    const VIDEO_FLV = 'video/x-flv';
    const VIDEO_WMV = 'video/x-ms-wmv';

    /** vendor-specific */
    const VND_OPENDOCUMENT_TEXT = 'application/vnd.oasis.opendocument.text';
    const VND_OPENDOCUMENT_SPREADSHEET = 'application/vnd.oasis.opendocument.spreadsheet';
    const VND_OPENDOCUMENT_PRESENTATION = 'application/vnd.oasis.opendocument.presentation';
    const VND_OPENDOCUMENT_GRAPHICS = 'application/vnd.oasis.opendocument.graphics';
    const VND_EXCEL = 'application/vnd.ms-excel';
    const VND_EXCEL_2007 = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    const VND_POWERPOINT = 'application/vnd.ms-powerpoint';
    const VND_POWERPOINT_2007 = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
    const VND_WORD_2007 = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    const VND_DART = 'application/vnd.dart';
    const VND_XPS = 'application/vnd.ms-xpsdocument';
    const VND_APK = 'application/vnd.android.package-archive';

}