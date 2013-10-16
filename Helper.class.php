<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Helper
 *
 * @author Fidel
 */
class Helper {

    //put your code here

    public static function array_htmlentities(&$value) {
        if (is_string($value))
            $value = htmlentities($value);
    }

    /**
     * this fonction apply the fellow actions :
     * - strtolower,
     * - remove non alpha-numeric caracters else '-'.
     * - replace multiple '-' with a single one.
     * @param string $str
     * @return string 
     */
    public static function slugify($str) {
        if (!$str)
            return $str;
        $str = preg_replace('/[^a-zA-Z0-9 -]/', '', $str);
        $str = strtolower(str_replace(' ', '-', trim($str)));
        $str = preg_replace('/-+/', '-', $str);
        return $str;
    }

    public static function spell_number($number) {
        if (($number < 0) || ($number > 999999999)) {
            throw new Exception("Number is out of range");
        }

        $Gn = floor($number / 1000000);  /* Millions (giga) */
        $number -= $Gn * 1000000;
        $kn = floor($number / 1000);     /* Thousands (kilo) */
        $number -= $kn * 1000;
        $Hn = floor($number / 100);      /* Hundreds (hecto) */
        $number -= $Hn * 100;
        $Dn = floor($number / 10);       /* Tens (deca) */
        $n = $number % 10;               /* Ones */

        $res = "";

        if ($Gn) {
            $res .= $Gn . " Million";
        }

        if ($kn) {
            $res .= (empty($res) ? "" : " ") .
                    $kn . " Thousand";
        }

        if ($Hn) {
            $res .= (empty($res) ? "" : " ") .
                    $Hn . " Hundred";
        }

        $ones = array("", "One", "Two", "Three", "Four", "Five", "Six",
            "Seven", "Eight", "Nine", "Ten", "Eleven", "Twelve", "Thirteen",
            "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eightteen",
            "Nineteen");
        $tens = array("", "", "Twenty", "Thirty", "Fourty", "Fifty", "Sixty",
            "Seventy", "Eigthy", "Ninety");

        if ($Dn || $n) {
            if (!empty($res)) {
                $res .= " and ";
            }

            if ($Dn < 2) {
                $res .= $ones[$Dn * 10 + $n];
            } else {
                $res .= $tens[$Dn];

                if ($n) {
                    $res .= "-" . $ones[$n];
                }
            }
        }

        if (empty($res)) {
            $res = "zero";
        }

        return $res;
    }

    public static function load_lib($n, $f = null) {
        return extension_loaded($n) or dl(((PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '') . ($f ? $f : $n) . '.' . PHP_SHLIB_SUFFIX);
    }

    public static function current_full_path() {
        $root = $_SERVER['DOCUMENT_ROOT'];
        $self = $_SERVER['PHP_SELF'];
        return $root . mb_substr($self, 0, -mb_strlen(strrchr($self, "/")));
    }

    /**
     * This function will check whether the visitor is a search engine robot 
     */
    public static function is_bot() {
        $botlist = array(
            "Teoma",
            "alexa",
            "froogle",
            "Gigabot",
            "inktomi",
            "looksmart",
            "URL_Spider_SQL",
            "Firefly",
            "NationalDirectory",
            "Ask Jeeves",
            "TECNOSEEK",
            "InfoSeek",
            "WebFindBot",
            "girafabot",
            "crawler",
            "www.galaxy.com",
            "Googlebot",
            "Scooter",
            "Slurp",
            "msnbot",
            "appie",
            "FAST",
            "WebBug",
            "Spade",
            "ZyBorg",
            "rabaz",
            "Baiduspider",
            "Feedfetcher-Google",
            "TechnoratiSnoop",
            "Rankivabot",
            "Mediapartners-Google",
            "Sogou web spider",
            "WebAlta Crawler",
            "TweetmemeBot",
            "Butterfly",
            "Twitturls",
            "Me.dium",
            "Twiceler"
        );

        foreach ($botlist as $bot) {
            if (strpos($_SERVER['HTTP_USER_AGENT'], $bot) !== false)
                return true; // Is a bot
        }

        return false; // Not a bot
    }

    public static function get_tag($tag, $xml) {
        preg_match_all('/<' . $tag . '>(.*)<\/' . $tag . '>$/imU', $xml, $match);
        return $match[1];
    }

    /* text_helper */

    public static function word_censor($str, $censored, $replacement = '') {
        if (!is_array($censored)) {
            return $str;
        }

        $str = ' ' . $str . ' ';

        // \w, \b and a few others do not match on a unicode character
        // set for performance reasons. As a result words like �ber
        // will not match on a word boundary. Instead, we'll assume that
        // a bad word will be bookeneded by any of these characters.
        $delim = '[-_\'\"`(){}<>\[\]|!?@#%&,.:;^~*+=\/ 0-9\n\r\t]';

        foreach ($censored as $badword) {
            if ($replacement != '') {
                $str = preg_replace("/({$delim})(" . str_replace('\*', '\w*?', preg_quote($badword, '/')) . ")({$delim})/i", "\\1{$replacement}\\3", $str);
            } else {
                $str = preg_replace("/({$delim})(" . str_replace('\*', '\w*?', preg_quote($badword, '/')) . ")({$delim})/ie", "'\\1'.str_repeat('#', strlen('\\2')).'\\3'", $str);
            }
        }

        return trim($str);
    }

    public static function reduce_multiples($str, $character = ',', $trim = FALSE) {
        $str = preg_replace('#' . preg_quote($character, '#') . '{2,}#', $character, $str);

        if ($trim === TRUE) {
            $str = trim($str, $character);
        }

        return $str;
    }

    public static function random_string($type = 'alnum', $len = 8) {
        switch ($type) {
            case 'basic' : return mt_rand();
                break;
            case 'alnum' :
            case 'numeric' :
            case 'nozero' :
            case 'alpha' :

                switch ($type) {
                    case 'alpha' : $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'alnum' : $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'numeric' : $pool = '0123456789';
                        break;
                    case 'nozero' : $pool = '123456789';
                        break;
                }

                $str = '';
                for ($i = 0; $i < $len; $i++) {
                    $str .= substr($pool, mt_rand(0, strlen($pool) - 1), 1);
                }
                return $str;
                break;
            case 'unique' :
            case 'md5' :

                return md5(uniqid(mt_rand()));
                break;
            case 'encrypt' :
            case 'sha1' :

                $CI = & get_instance();
                $CI->load->helper('security');

                return do_hash(uniqid(mt_rand(), TRUE), 'sha1');
                break;
        }
    }

    /* security_helper */

    public static function strip_image_tags($str) {
        $str = preg_replace("#<img\s+.*?src\s*=\s*[\"'](.+?)[\"'].*?\>#", "\\1", $str);
        $str = preg_replace("#<img\s+.*?src\s*=\s*(.+?).*?\>#", "\\1", $str);

        return $str;
    }

    public static function encode_php_tags($str) {
        return str_replace(array('<?php', '<?PHP', '<?', '?>'), array('&lt;?php', '&lt;?PHP', '&lt;?', '?&gt;'), $str);
    }

    /*
     * files
     */

    public static function byte_format($num, $precision = 1) {

        if ($num >= 1000000000000) {
            $num = round($num / 1099511627776, $precision);
            $unit = 'Tb';
        } elseif ($num >= 1000000000) {
            $num = round($num / 1073741824, $precision);
            $unit = 'Gb';
        } elseif ($num >= 1000000) {
            $num = round($num / 1048576, $precision);
            $unit = 'Mb';
        } elseif ($num >= 1000) {
            $num = round($num / 1024, $precision);
            $unit = 'Kb';
        } else {
            $unit = 'bytes';
            return number_format($num) . ' ' . $unit;
        }

        return number_format($num, $precision) . ' ' . $unit;
    }

    public static function meta($name = '', $content = '', $type = 'name', $newline = "\n") {
        // Since we allow the data to be passes as a string, a simple array
        // or a multidimensional one, we need to do a little prepping.
        if (!is_array($name)) {
            $name = array(array('name' => $name, 'content' => $content, 'type' => $type, 'newline' => $newline));
        } else {
            // Turn single array into multidimensional
            if (isset($name['name'])) {
                $name = array($name);
            }
        }

        $str = '';
        foreach ($name as $meta) {
            $type = (!isset($meta['type']) OR $meta['type'] == 'name') ? 'name' : 'http-equiv';
            $name = (!isset($meta['name'])) ? '' : $meta['name'];
            $content = (!isset($meta['content'])) ? '' : $meta['content'];
            $newline = (!isset($meta['newline'])) ? "\n" : $meta['newline'];

            $str .= '<meta ' . $type . '="' . $name . '" content="' . $content . '" />' . $newline;
        }

        return $str;
    }

    public static function get_mime_by_extension($file) {
        $mimes = array(
            'hqx' => 'application/mac-binhex40',
            'cpt' => 'application/mac-compactpro',
            'csv' => array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel'),
            'bin' => 'application/macbinary',
            'dms' => 'application/octet-stream',
            'lha' => 'application/octet-stream',
            'lzh' => 'application/octet-stream',
            'exe' => array('application/octet-stream', 'application/x-msdownload'),
            'class' => 'application/octet-stream',
            'psd' => 'application/x-photoshop',
            'so' => 'application/octet-stream',
            'sea' => 'application/octet-stream',
            'dll' => 'application/octet-stream',
            'oda' => 'application/oda',
            'pdf' => array('application/pdf', 'application/x-download'),
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',
            'smi' => 'application/smil',
            'smil' => 'application/smil',
            'mif' => 'application/vnd.mif',
            'xls' => array('application/excel', 'application/vnd.ms-excel', 'application/msexcel'),
            'ppt' => array('application/powerpoint', 'application/vnd.ms-powerpoint'),
            'wbxml' => 'application/wbxml',
            'wmlc' => 'application/wmlc',
            'dcr' => 'application/x-director',
            'dir' => 'application/x-director',
            'dxr' => 'application/x-director',
            'dvi' => 'application/x-dvi',
            'gtar' => 'application/x-gtar',
            'gz' => 'application/x-gzip',
            'php' => 'application/x-httpd-php',
            'php4' => 'application/x-httpd-php',
            'php3' => 'application/x-httpd-php',
            'phtml' => 'application/x-httpd-php',
            'phps' => 'application/x-httpd-php-source',
            'js' => 'application/x-javascript',
            'swf' => 'application/x-shockwave-flash',
            'sit' => 'application/x-stuffit',
            'tar' => 'application/x-tar',
            'tgz' => array('application/x-tar', 'application/x-gzip-compressed'),
            'xhtml' => 'application/xhtml+xml',
            'xht' => 'application/xhtml+xml',
            'rar' => array('application/x-zip', 'application/zip', 'application/x-zip-compressed'),
            'zip' => array('application/x-zip', 'application/zip', 'application/x-zip-compressed'),
            'mid' => 'audio/midi',
            'midi' => 'audio/midi',
            'mpga' => 'audio/mpeg',
            'mp2' => 'audio/mpeg',
            'mp3' => array('audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'),
            'aif' => 'audio/x-aiff',
            'aiff' => 'audio/x-aiff',
            'aifc' => 'audio/x-aiff',
            'ram' => 'audio/x-pn-realaudio',
            'rm' => 'audio/x-pn-realaudio',
            'rpm' => 'audio/x-pn-realaudio-plugin',
            'ra' => 'audio/x-realaudio',
            'rv' => 'video/vnd.rn-realvideo',
            'wav' => array('audio/x-wav', 'audio/wave', 'audio/wav'),
            'bmp' => array('image/bmp', 'image/x-windows-bmp'),
            'gif' => 'image/gif',
            'jpeg' => array('image/jpeg', 'image/pjpeg'),
            'jpg' => array('image/jpeg', 'image/pjpeg'),
            'jpe' => array('image/jpeg', 'image/pjpeg'),
            'png' => array('image/png', 'image/x-png'),
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'css' => 'text/css',
            'html' => 'text/html',
            'htm' => 'text/html',
            'shtml' => 'text/html',
            'txt' => 'text/plain',
            'text' => 'text/plain',
            'log' => array('text/plain', 'text/x-log'),
            'rtx' => 'text/richtext',
            'rtf' => 'text/rtf',
            'xml' => 'text/xml',
            'xsl' => 'text/xml',
            'mpeg' => 'video/mpeg',
            'mpg' => 'video/mpeg',
            'mpe' => 'video/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo',
            'movie' => 'video/x-sgi-movie',
            'doc' => 'application/msword',
            'docx' => array('application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            'xlsx' => array('application/msexcel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            'word' => array('application/msword', 'application/octet-stream'),
            'xl' => 'application/excel',
            'eml' => 'message/rfc822',
            'json' => array('application/json', 'text/json')
        );
        $extension = strtolower(substr(strrchr($file, '.'), 1));

        if (array_key_exists($extension, $mimes)) {
            if (is_array($mimes[$extension])) {
                // Multiple mime types, just give the first one
                return current($mimes[$extension]);
            } else {
                return $mimes[$extension];
            }
        } else {
            return FALSE;
        }
    }

    public static function get_file_info($file, $returned_values = array('name', 'server_path', 'size', 'date')) {

        if (!file_exists($file)) {
            return FALSE;
        }

        if (is_string($returned_values)) {
            $returned_values = explode(',', $returned_values);
        }

        foreach ($returned_values as $key) {
            switch ($key) {
                case 'name':
                    $fileinfo['name'] = substr(strrchr($file, DIRECTORY_SEPARATOR), 1);
                    break;
                case 'server_path':
                    $fileinfo['server_path'] = $file;
                    break;
                case 'size':
                    $fileinfo['size'] = filesize($file);
                    break;
                case 'date':
                    $fileinfo['date'] = filemtime($file);
                    break;
                case 'readable':
                    $fileinfo['readable'] = is_readable($file);
                    break;
                case 'writable':
                    // There are known problems using is_weritable on IIS.  It may not be reliable - consider fileperms()
                    $fileinfo['writable'] = is_writable($file);
                    break;
                case 'executable':
                    $fileinfo['executable'] = is_executable($file);
                    break;
                case 'fileperms':
                    $fileinfo['fileperms'] = fileperms($file);
                    break;
            }
        }

        return $fileinfo;
    }

    public static function get_dir_file_info($source_dir, $top_level_only = TRUE, $_recursion = FALSE) {
        static $_filedata = array();
        $relative_path = $source_dir;

        if ($fp = @opendir($source_dir)) {
            // reset the array and make sure $source_dir has a trailing slash on the initial call
            if ($_recursion === FALSE) {
                $_filedata = array();
                $source_dir = rtrim(realpath($source_dir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            }

            // foreach (scandir($source_dir, 1) as $file) // In addition to being PHP5+, scandir() is simply not as fast
            while (FALSE !== ($file = readdir($fp))) {
                if (@is_dir($source_dir . $file) AND strncmp($file, '.', 1) !== 0 AND $top_level_only === FALSE) {
                    get_dir_file_info($source_dir . $file . DIRECTORY_SEPARATOR, $top_level_only, TRUE);
                } elseif (strncmp($file, '.', 1) !== 0) {
                    $_filedata[$file] = get_file_info($source_dir . $file);
                    $_filedata[$file]['relative_path'] = $relative_path;
                }
            }

            return $_filedata;
        } else {
            return FALSE;
        }
    }

    public static function get_filenames($source_dir, $include_path = FALSE, $_recursion = FALSE) {
        static $_filedata = array();

        if ($fp = @opendir($source_dir)) {
            // reset the array and make sure $source_dir has a trailing slash on the initial call
            if ($_recursion === FALSE) {
                $_filedata = array();
                $source_dir = rtrim(realpath($source_dir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            }

            while (FALSE !== ($file = readdir($fp))) {
                if (@is_dir($source_dir . $file) && strncmp($file, '.', 1) !== 0) {
                    get_filenames($source_dir . $file . DIRECTORY_SEPARATOR, $include_path, TRUE);
                } elseif (strncmp($file, '.', 1) !== 0) {
                    $_filedata[] = ($include_path == TRUE) ? $source_dir . $file : $file;
                }
            }
            return $_filedata;
        } else {
            return FALSE;
        }
    }

    public static function force_download($filename = '', $data = '') {
        if ($filename == '' OR $data == '') {
            return FALSE;
        }

        // Try to determine if the filename includes a file extension.
        // We need it in order to set the MIME type
        if (FALSE === strpos($filename, '.')) {
            return FALSE;
        }

        // Grab the file extension
        $x = explode('.', $filename);
        $extension = end($x);

        // Load the mime types
        if (defined('ENVIRONMENT') AND is_file(APPPATH . 'config/' . ENVIRONMENT . '/mimes.php')) {
            include(APPPATH . 'config/' . ENVIRONMENT . '/mimes.php');
        } elseif (is_file(APPPATH . 'config/mimes.php')) {
            include(APPPATH . 'config/mimes.php');
        }

        // Set a default mime if we can't find it
        if (!isset($mimes[$extension])) {
            $mime = 'application/octet-stream';
        } else {
            $mime = (is_array($mimes[$extension])) ? $mimes[$extension][0] : $mimes[$extension];
        }

        // Generate the server headers
        if (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") !== FALSE) {
            header('Content-Type: "' . $mime . '"');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header("Content-Transfer-Encoding: binary");
            header('Pragma: public');
            header("Content-Length: " . strlen($data));
        } else {
            header('Content-Type: "' . $mime . '"');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header("Content-Transfer-Encoding: binary");
            header('Expires: 0');
            header('Pragma: no-cache');
            header("Content-Length: " . strlen($data));
        }

        exit($data);
    }

    public static function directory_map($source_dir, $directory_depth = 0, $hidden = FALSE) {
        if ($fp = @opendir($source_dir)) {
            $filedata = array();
            $new_depth = $directory_depth - 1;
            $source_dir = rtrim($source_dir, '/') . '/';

            while (FALSE !== ($file = readdir($fp))) {
                // Remove '.', '..', and hidden files [optional]
                if (!trim($file, '.') OR ($hidden == FALSE && $file[0] == '.')) {
                    continue;
                }

                if (($directory_depth < 1 OR $new_depth > 0) && @is_dir($source_dir . $file)) {
                    $filedata[$file] = directory_map($source_dir . $file . '/', $new_depth, $hidden);
                } else {
                    $filedata[] = $file;
                }
            }

            closedir($fp);
            return $filedata;
        }

        return FALSE;
    }

// --------------------------------------------------------------------

    /**
     * Camelize
     *
     * Takes multiple words separated by spaces or underscores and camelizes them
     *
     * @access	public
     * @param	string
     * @return	str
     */
    public static function camelize($str) {
        $str = 'x' . strtolower(trim($str));
        $str = ucwords(preg_replace('/[\s_]+/', ' ', $str));
        return substr(str_replace(' ', '', $str), 1);
    }

    /**
     * Underscore
     *
     * Takes multiple words separated by spaces and underscores them
     *
     * @access	public
     * @param	string
     * @return	str
     */
    public static function underscore($str) {
        return preg_replace('/[\s-]+/', '_', strtolower(trim($str)));
    }

// --------------------------------------------------------------------

    /**
     * Humanize
     *
     * Takes multiple words separated by underscores and changes them to spaces
     *
     * @access	public
     * @param	string
     * @return	str
     */
    public static function humanize($str) {
        return ucwords(preg_replace('/[_]+/', ' ', strtolower(trim($str))));
    }

    public static function htmlcolor2rgb($htmlcol) {
        $offset = 0;
        if ($htmlcol{0} == '#')
            $offset = 1;
        $r = hexdec(substr($htmlcol, $offset, 2));
        $g = hexdec(substr($htmlcol, $offset + 2, 2));
        $b = hexdec(substr($htmlcol, $offset + 4, 2));
        return array($r, $g, $b);
    }

// arrays
    public static function array_key_as_value(array $array) {
        if (!count($array))
            return $array;
        $array = array_combine(array_values($array), array_values($array));
        return $array;
    }

    public static function array_value_as_key(array $array) {
        $array = array_combine(array_keys($array), array_keys($array));
        return $array;
    }

    public static function trim_array_value(&$value) {
        $value = trim($value);
    }

    public static function serialize_array($array, $delimiter = ',') {
        if (!is_array($array))
            return '';
        return join($delimiter, $array);
    }

    public static function unserialize_array($str, $delimiter = ',') {
        if (is_array($str))
            return $str;

        $value = trim($str);
        if ($value == '')
            return array();

        $value = trim($str, $delimiter);
        if (is_string($value) && $value != '') {
            $array = explode($delimiter, $value);
            array_walk($array, 'Helper::trim_array_value');
            return $array;
        }
        if (is_integer($value)) {
            return array($value);
        }

        return array();
    }

    public static function array_to_lines($array) {
        $lines = implode("\r\n", $array);
        return $lines;
    }

    public static function lines_to_array($lines) {
        $array = str_replace("\r\n", "\n", $lines);
        $array = array_filter(explode("\n", $array));
        return $array;
    }

    /**
     *
     * @param array $array
     * @param string $key
     * @return number 
     */
    public static function array_sum_values(array $array, $key = NULL) {
        $sum = 0;
        foreach ($array as $value) {
            $sum += ($key) ? intval($value[$key]) : intval($value);
        }
        return $sum;
    }

    /*     * ******************************************************************
      RUN PHP CODE */

    public static function run_php_ajax() {
        global $DB, $app;
        set_ajax(1, 1);
        $code = get_post_var('code', 0, '');
        switch (get_post_var('script', 0, '')) {
            case 'sql' :
                if ($result = $app->db->direct_query($code))
                    return $result;
                else
                    return _b($app->db->last_error_no . ': ') . $app->db->last_error;
                break;

            case 'printr' :
                $out = eval("return printr($code) ;");
                $out = $app->render_messages() . $out;
                break;

            default :
                $out = eval($code);
                $out = $app->render_messages() . $out;
        }
        return $out;
    }

    public static function run_php_form() {
        $out = "<div class='ajax-response' id='php-run'></div>";
        $form = array(
            'options' => array(
                'id' => 'run-php-form',
                'submitLabel' => rv_t('execute'),
            ),
            'fields' => array(
                'code' => array(
                    'type' => 'text',
                    'label' => '',
                ),
                'script' => array(
                    'type' => 'choices',
                    'caption' => 'execute as SQL code',
                    'choices' => array(
                        'php' => 'execute as php code',
                        'sql' => 'execute as SQL code',
                        'printr' => 'show var dump',
                    ),
                    'multiple' => 0,
                    'values' => 'sql',
                ),
            )
        );
        $out .= render_form($form);
        return $out;
    }

    public static function run_php() {
        global $DB, $app;
        set_page_title(rv_t('run php'));
        $out = '';
        $code = get_post_var('code', 0, '');
        switch (get_post_var('sql', 0, '')) {
            case 'sql' :
                global $app;
                if ($result = $app->db->direct_query($code))
                    $out .= $result;
                else
                    $out .= _b($app->db->last_error_no . ': ') . $app->db->last_error;
                break;

            case 'printr' :
                $out .= $app->render_messages();
                $out .= eval("return printr($code) ;");
                break;

            default :
                $out .= $app->render_messages();
                $out .= eval($code);
        }

        $out .= run_php_form();
        return $out;
    }

    /*     * ******************************************************************
      INTERNALS */

    /*     * ******************************************************************
      GLOBALS */

    public static function is_front() {
        global $app;
        return $app->path->base_url == '';
    }

    public static function sys_config_globals() {
        global $app;
        global $app;
        $out = '';
        set_page_title(rv_t('configure globals'));
        set_local_link('admin/config/globals/add', rv_t('Add global variable'));
        if (form_submitted('config-globals-form')) {
            foreach ($_POST as $key => $value) {
                if (is_numeric($key)) {
                    $app->db->update('globals', array('value' => self::enclose_var($value)), "id = $key");
                }
            }
            set_hint(rv_t('Global variables updated.'));
        }
        // FORM
        if (!$globals = $app->db->get_rows('globals', '*')) {
            set_error(rv_t('No globals found'));
        } else {
            $form = new form();
            $form->open(array('id' => 'config-globals-form'));
            foreach ($globals as $global) {
                $form->addInput('text', array('label' => $global['variable'] . ' :', 'name' => $global['id'], 'value' => $global['value']));
            }
            $form->addInput('submit', array('name' => 'submit', 'value' => rv_t('Save')));
            $form->close();
            $out .= $form;
            $form->free();
        }
        return $out;
    }

    public static function sys_add_global() {
        global $app;
        $out = '';
        set_page_title(rv_t('add global variable'));
        if (!form_submitted('add-global-form') || error_found()) {
            $form = new form();
            $form->open(array('id' => 'add-global-form'));
            $form->addInput('text', array('label' => '', 'name' => 'variable'));
            $form->addInput('text', array('label' => '', 'name' => 'value'));
            $form->addInput('submit', array('name' => 'save', 'value' => rv_t('Save')));
            $form->close();
            $out .= $form->render();
            $form->free();
        } else {
            $variable = get_post_var('variable');
            $value = get_post_var('value');
            analyse_error('variable', $variable, 'required');
            analyse_error('variable', $variable, 'unique', array('globals', 'variable'));
            if (error_found())
                return sysAddGlobal();
            else {
                if ($app->db->insert('globals', array('variable' => self::enclose_var($variable), 'value' => self::enclose_var($value))))
                    rv_redirect_to('admin/config/globals');
                else
                    set_error(rv_t('Database error !!'));
            }
        }
        return $out;
    }

    public static function get_global_permissions() {
        global $app;
        if (!$perms = $app->db->get_value('globals', 'value', "variable = 'global_permissions'"))
            return array();
        else {
            $perms = unserialize_array($perms);
            return $perms;
        }
    }

    /*     * ******************************************************************
      system */

// FILES
// HEADER
    public static function set_header($var, $value = '') {
        global $app;
        $app->header[$var] = $value;
    }

// FONCTIONS
    public static function get_function_content() {
        set_ajax(!(get_request_var('fromGetFunctionContentPage', 0, 0)));
        $cb = ($cb) ? $cb : get_request_var('callback');
        $args = ($args) ? $args : get_request_var('args', 0, '');
        $args = unserialize_array($args);
        $args = (array) $args;
        if (is_callable($cb)) {
            return call_user_func($cb, $args);
        } else {
            return 'function not found: ' . _b($cb);
        }
    }

    public static function get_function_content_form($fill = array()) {
        $form = createForm(
                array(
                    'options' => array(
                        'id' => 'gfc-form',
                        'fill' => $fill,
                    ),
                    'fields' => array(
                        'fromGetFunctionContentPage' => array(
                            'type' => 'hidden',
                            'value' => 1,
                        ),
                        'callback' => array(
                            'type' => 'string',
                            'label' => rv_t('callback function'),
                        ),
                        'args' => array(
                            'type' => 'string',
                            'label' => rv_t('arguments'),
                        ),
                    )
                )
        );

        return $form;
    }

    public static function get_function_content_page() {
        set_page_title(rv_t('get function content'));
        $out = '';
        if (form_submitted('gfc-form')) {
            $result = getFunctionContent();
            $out .= _div(_h(rv_t('Result :'), 3) . $result, array('class' => ' result in'));
        }
        // FORM
        $out .= getFunctionContentForm()->render();
        // OUT
        return $out;
    }

    /*     * ******************************************************************
      SECURITY */


    /*     * ******************************************************************
      VARIABLES */

    public static function constants($cat = 'user') {
        $cons = get_defined_constants(1);
        if (!$cat)
            return $cons;
        $cons = $cons[$cat];
        return $cons;
    }

// public variables
// text
    /**
     *
     * @param string $html
     * @return string 
     */
    public static function extract_html_text($html) {
        $html_reg = '/<+\s*\/*\s*([A-Z][A-Z0-9]*)\b[^>]*\/*\s*>+/i';
        // $text = htmlentities( preg_replace( $html_reg, '', $html ) );
        $text = str_replace('&nbsp;', ' ', strip_tags($html));
        return $text;
    }

    /**
     *
     * @param string $text
     * @param int $resume_enght default: 800
     * @return string 
     */
    public static function rv_extract_brief($text, $resume_length = 800) {
        $clean_text = extract_html_text($text);
        if (strlen($clean_text) > $resume_length) {
            $lenght = strpos($clean_text, ' ', $resume_length);
            $lenght = ($lenght) ? $lenght : $resume_length;
            $resume = substr($clean_text, 0, $lenght) . ' ...';
        } else {
            $resume = $clean_text;
        }
        return $resume;
    }

    /**
     *
     * @param type $chaine
     * @param type $max
     * @param type $separateur
     * @param type $suffix
     * @return string 
     */
    public static function tronqate_html($chaine, $max, $separateur = ' ', $suffix = ' ...') {
        // RETOURNE UNE CHAINE AVEC LE NOMBRE MAXIMUM DE MOTS PASSE EN PRAMETRE
        // NEED "HTMLPARSER" CLASS
        if (strlen(strip_tags($chaine)) > $max) {
            $parser = new HtmlParser($chaine);
            $tabElements = array();
            $cur_len = 0;
            while ($parser->parse()) {
                if ($parser->iNodeType == NODE_TYPE_ELEMENT) {
                    array_push($tabElements, $parser->iNodeName);
                } elseif ($parser->iNodeType == NODE_TYPE_ENDELEMENT) {
                    while (array_pop($tabElements) != $parser->iNodeName) {
                        if (count($tabElements) < 1) {
                            echo 'Erreur : pas de balise ouvrante pour ' .
                            $parser->iNodeName;
                        }
                    }
                } elseif ($parser->iNodeType == NODE_TYPE_TEXT) {
                    $cur_max = $cur_len + $parser->iNodeEnd -
                            $parser->iNodeStart;
                    if ($cur_max == $max) {
                        $resultat = substr($chaine, 0, $parser->iNodeEnd) . $suffix;
                        while (($balise = array_pop($tabElements)) !== null) {
                            $balise = (strtolower($balise) == 's') ? 'strong' : $balise;
                            $resultat .= '</' . $balise . '>';
                        }
                        return $resultat;
                    } elseif ($cur_max > $max) {
                        if (($pos = strrpos(substr($parser->iNodeValue, 0, ($max - $cur_len + strlen($separateur))), $separateur)) !== false) {
                            $resultat = substr($chaine, 0, $parser->iNodeStart +
                                            $pos) . $suffix;
                            while (($balise = array_pop($tabElements)) !== null) {
                                $balise = (strtolower($balise) == 's') ? 'strong' : $balise;
                                $resultat .= '</' . $balise . '>';
                            }
                            return $resultat;
                        } else {
                            $resultat = substr($chaine, 0, $parser->iNodeEnd) . $suffix;
                            while (($balise = array_pop($tabElements)) !== null) {
                                $balise = (strtolower($balise) == 's') ? 'strong' : $balise;
                                $resultat .= '</' . $balise . '>';
                            }
                            return $resultat;
                        }
                    } else {
                        $cur_len += $parser->iNodeEnd - $parser->iNodeStart;
                    }
                }
            }
        }
        return $chaine;
    }

    /**
     *
     * @param type $p_chaine
     * @param type $p_maxWord
     * @return type 
     */
    public static function get_max_word($p_chaine, $p_maxWord) {
        $array = explode(' ', $p_chaine);
        if (count($array) <= $p_maxWord) {
            return $p_chaine;
        }

        $chaine = "";
        for ($i = 0; $i < $p_maxWord; $i++) {
            $chaine .= $array[$i] . " ";
        }
        return $chaine . "...";
    }

// date manipulation
    /**
     *
     * @param mixte $dateTime
     * @return type 
     */
    public static function parse_date($dateTime = "") {
        $ts = 0; // timestamp of parsed date
        switch (gettype($dateTime)) {
            case "string": // <== modify to accomodate more date formats
                $ts = strtotime(str_replace('/', '-', $dateTime));
                break;
            case "integer":
                $ts = $dateTime;
                break;
            case "object":
                if (get_class($dateTime) == "dateclass") {
                    $ts = $dateTime->TimeStamp();
                }
        } //switch				
        return $ts;
    }

//_parseDate()
    /**
     *
     * @param type $time
     * @return type 
     */
    public static function format_time_ago($time) {
        $time_difference = time() - $time;
        //
        $seconds = $time_difference;
        $minutes = round($time_difference / 60);
        $hours = round($time_difference / 3600);
        $days = round($time_difference / 86400);
        $weeks = round($time_difference / 604800);
        $months = round($time_difference / 2419200);
        $years = round($time_difference / 29030400);
        // Seconds
        if ($seconds <= 60) {
            return format(rv_t("%s seconds ago"), html::span($seconds));
        }
        //Minutes
        else if ($minutes <= 60) {
            if ($minutes == 1)
                return rv_t("one minute ago");
            else
                return format(rv_t("%s minutes ago"), html::span($minutes));
        }
        //Hours
        else if ($hours <= 24) {
            if ($hours == 1)
                return rv_t("one hour ago");
            else
                return format(rv_t("%s hours ago"), html::span($hours));
        }
        //Days
        else if ($days <= 7) {
            if ($days == 1)
                return rv_t("one day ago");
            else
                return format(rv_t("%s days ago"), html::span($days));
        }

        //Weeks
        else if ($weeks <= 4) {
            if ($weeks == 1)
                return rv_t("one week ago");
            else
                return format(rv_t("%s weeks ago"), html::span($weeks));
        }

        //Months
        else if ($months <= 12) {
            if ($months == 1)
                return rv_t("one month ago");
            else
                return format(rv_t("%s months ago"), html::span($months));
        }

        //Years
        else {
            if ($years == 1)
                return rv_t("one year ago");
            else
                return format(rv_t("%s years ago"), html::span($years));
        }
    }

    /**
     * this function taken from Drupal ( drupal.org )
     * @param mix $var
     * @return string 
     */
    public static function json($var = false) {
        switch (gettype($var)) {
            case 'boolean':
                return $var ? 'true' : 'false'; // Lowercase necessary!
            case 'integer':
            case 'double':
                return $var;
            case 'resource':
            case 'string':
                return '"' . str_replace(array("\r", "\n", "<", ">", "&"), array('\r', '\n', '\x3c', '\x3e', '\x26'), addslashes($var)) . '"';
            case 'array':
                if (empty($var) || array_keys($var) === range(0, sizeof($var) - 1)) {
                    $output = array();
                    foreach ($var as $v) {
                        $output[] = json($v);
                    }
                    return '[ ' . implode(', ', $output) . ' ]';
                }
            // Otherwise, fall through to convert the array as an object.
            case 'object':
                $output = array();
                foreach ($var as $k => $v) {
                    $output[] = json(strval($k)) . ': ' . json($v);
                }
                return '{ ' . implode(', ', $output) . ' }';
            default:
                return 'null';
        }
    }

// url manipulation
    /**
     *
     * @param type $path
     * @return string 
     */
    public static function get_url_target($path) {
        if (substr($path, 0, 1) == '?' || substr($path, 0, 1) == '#')
            $type = 'local';
        elseif (preg_match("#(((https?|ftp)://(w{3}.)?)(?<!www)(w+-?)*.([a-z]{2,4}))#", $path))
            $type = 'external';
        else
            $type = 'internal';
        //
        return $type;
    }

    /**
     *
     * @param type $texte
     * @return type 
     */
    public static function format_for_url($texte) {
        /* suppression des espaces en début et fin de chaîne */
        $texte = trim($texte);

        /* suppression des espaces et car. non-alphanumériques */
        $texte = str_replace(" ", '-', $texte);

        /* Remove \ / | : ? * " < > */
        // $texte = preg_replace( '/\\\\|\\/|\\||\\:|\\?|\\*|"|<|>|[[:cntrl:]]/', '_', $texte ) ;

        return $texte;

        /* mise en minuscule */
        // $texte = strtolower($texte);


        /* suppression des accents, tréma et cédilles + qlq autres car. spéciaux */
        $texte = utf8_decode($texte);

        $texte = preg_replace('#([^A-Za-z0-9-_])#', '-', $texte);

        /* suppression des tirets multiples */
        $texte = preg_replace('#([-]+)#', '-', $texte);

        /* ici vous pouvez couper les tirets de début et fin de chaine */
        /* voir : http://blog.darklg.fr/94/nettoyer-une-chaine-pour-une-url-en-php/ */

        return $texte;
    }

    /**
     *
     * @param type $path
     * @return int 
     */
    public static function rv_is_link($path) {
        if (preg_match("#(((https?|ftp)://(w{3}.)?)(?<!www)(w+-?)*.([a-z]{2,4}))#", $path))
            return 1;
        else
            return 0;
    }

// strings manipulation
    /**
     *
     * @param type $str
     * @return type 
     */
    public static function replace_special_char($str) {
        $ch0 = array(
            "œ" => "oe",
            "Œ" => "OE",
            "?" => "ae",
            "?" => "AE",
            "A" => "A",
            "?" => "A",
            "A" => "A",
            "?" => "A",
            "?" => "A",
            "?" => "A",
            "&#256;" => "A",
            "&#258;" => "A",
            "&#461;" => "A",
            "&#7840;" => "A",
            "&#7842;" => "A",
            "&#7844;" => "A",
            "&#7846;" => "A",
            "&#7848;" => "A",
            "&#7850;" => "A",
            "&#7852;" => "A",
            "&#7854;" => "A",
            "&#7856;" => "A",
            "&#7858;" => "A",
            "&#7860;" => "A",
            "&#7862;" => "A",
            "&#506;" => "A",
            "&#260;" => "A",
            "à" => "a",
            "?" => "a",
            "â" => "a",
            "?" => "a",
            "?" => "a",
            "?" => "a",
            "&#257;" => "a",
            "&#259;" => "a",
            "&#462;" => "a",
            "&#7841;" => "a",
            "&#7843;" => "a",
            "&#7845;" => "a",
            "&#7847;" => "a",
            "&#7849;" => "a",
            "&#7851;" => "a",
            "&#7853;" => "a",
            "&#7855;" => "a",
            "&#7857;" => "a",
            "&#7859;" => "a",
            "&#7861;" => "a",
            "&#7863;" => "a",
            "&#507;" => "a",
            "&#261;" => "a",
            "C" => "C",
            "&#262;" => "C",
            "&#264;" => "C",
            "&#266;" => "C",
            "&#268;" => "C",
            "ç" => "c",
            "&#263;" => "c",
            "&#265;" => "c",
            "&#267;" => "c",
            "&#269;" => "c",
            "?" => "D",
            "&#270;" => "D",
            "&#272;" => "D",
            "&#271;" => "d",
            "&#273;" => "d",
            "E" => "E",
            "E" => "E",
            "E" => "E",
            "E" => "E",
            "&#274;" => "E",
            "&#276;" => "E",
            "&#278;" => "E",
            "&#280;" => "E",
            "&#282;" => "E",
            "&#7864;" => "E",
            "&#7866;" => "E",
            "&#7868;" => "E",
            "&#7870;" => "E",
            "&#7872;" => "E",
            "&#7874;" => "E",
            "&#7876;" => "E",
            "&#7878;" => "E",
            "è" => "e",
            "é" => "e",
            "ê" => "e",
            "ë" => "e",
            "&#275;" => "e",
            "&#277;" => "e",
            "&#279;" => "e",
            "&#281;" => "e",
            "&#283;" => "e",
            "&#7865;" => "e",
            "&#7867;" => "e",
            "&#7869;" => "e",
            "&#7871;" => "e",
            "&#7873;" => "e",
            "&#7875;" => "e",
            "&#7877;" => "e",
            "&#7879;" => "e",
            "&#284;" => "G",
            "&#286;" => "G",
            "&#288;" => "G",
            "&#290;" => "G",
            "&#285;" => "g",
            "&#287;" => "g",
            "&#289;" => "g",
            "&#291;" => "g",
            "&#292;" => "H",
            "&#294;" => "H",
            "&#293;" => "h",
            "&#295;" => "h",
            "?" => "I",
            "?" => "I",
            "I" => "I",
            "I" => "I",
            "&#296;" => "I",
            "&#298;" => "I",
            "&#300;" => "I",
            "&#302;" => "I",
            "&#304;" => "I",
            "&#463;" => "I",
            "&#7880;" => "I",
            "&#7882;" => "I",
            "&#308;" => "J",
            "&#309;" => "j",
            "&#310;" => "K",
            "&#311;" => "k",
            "&#313;" => "L",
            "&#315;" => "L",
            "&#317;" => "L",
            "&#319;" => "L",
            "&#321;" => "L",
            "&#314;" => "l",
            "&#316;" => "l",
            "&#318;" => "l",
            "&#320;" => "l",
            "&#322;" => "l",
            "?" => "N",
            "&#323;" => "N",
            "&#325;" => "N",
            "&#327;" => "N",
            "?" => "n",
            "&#324;" => "n",
            "&#326;" => "n",
            "&#328;" => "n",
            "&#329;" => "n",
            "?" => "O",
            "?" => "O",
            "O" => "O",
            "?" => "O",
            "?" => "O",
            "?" => "O",
            "&#332;" => "O",
            "&#334;" => "O",
            "&#336;" => "O",
            "&#416;" => "O",
            "&#465;" => "O",
            "&#510;" => "O",
            "&#7884;" => "O",
            "&#7886;" => "O",
            "&#7888;" => "O",
            "&#7890;" => "O",
            "&#7892;" => "O",
            "&#7894;" => "O",
            "&#7896;" => "O",
            "&#7898;" => "O",
            "&#7900;" => "O",
            "&#7902;" => "O",
            "&#7904;" => "O",
            "&#7906;" => "O",
            "?" => "o",
            "?" => "o",
            "ô" => "o",
            "?" => "o",
            "?" => "o",
            "?" => "o",
            "&#333;" => "o",
            "&#335;" => "o",
            "&#337;" => "o",
            "&#417;" => "o",
            "&#466;" => "o",
            "&#511;" => "o",
            "&#7885;" => "o",
            "&#7887;" => "o",
            "&#7889;" => "o",
            "&#7891;" => "o",
            "&#7893;" => "o",
            "&#7895;" => "o",
            "&#7897;" => "o",
            "&#7899;" => "o",
            "&#7901;" => "o",
            "&#7903;" => "o",
            "&#7905;" => "o",
            "&#7907;" => "o",
            "?" => "o",
            "&#340;" => "R",
            "&#342;" => "R",
            "&#344;" => "R",
            "&#341;" => "r",
            "&#343;" => "r",
            "&#345;" => "r",
            "&#346;" => "S",
            "&#348;" => "S",
            "&#350;" => "S",
            "?" => "S",
            "&#347;" => "s",
            "&#349;" => "s",
            "&#351;" => "s",
            "?" => "s",
            "&#354;" => "T",
            "&#356;" => "T",
            "&#358;" => "T",
            "&#355;" => "t",
            "&#357;" => "t",
            "&#359;" => "t",
            "U" => "U",
            "?" => "U",
            "U" => "U",
            "U" => "U",
            "&#360;" => "U",
            "&#362;" => "U",
            "&#364;" => "U",
            "&#366;" => "U",
            "&#368;" => "U",
            "&#370;" => "U",
            "&#431;" => "U",
            "&#467;" => "U",
            "&#469;" => "U",
            "&#471;" => "U",
            "&#473;" => "U",
            "&#475;" => "U",
            "&#7908;" => "U",
            "&#7910;" => "U",
            "&#7912;" => "U",
            "&#7914;" => "U",
            "&#7916;" => "U",
            "&#7918;" => "U",
            "&#7920;" => "U",
            "ù" => "u",
            "?" => "u",
            "û" => "u",
            "ü" => "u",
            "&#361;" => "u",
            "&#363;" => "u",
            "&#365;" => "u",
            "&#367;" => "u",
            "&#369;" => "u",
            "&#371;" => "u",
            "&#432;" => "u",
            "&#468;" => "u",
            "&#470;" => "u",
            "&#472;" => "u",
            "&#474;" => "u",
            "&#476;" => "u",
            "&#7909;" => "u",
            "&#7911;" => "u",
            "&#7913;" => "u",
            "&#7915;" => "u",
            "&#7917;" => "u",
            "&#7919;" => "u",
            "&#7921;" => "u",
            "&#372;" => "W",
            "&#7808;" => "W",
            "&#7810;" => "W",
            "&#7812;" => "W",
            "&#373;" => "w",
            "&#7809;" => "w",
            "&#7811;" => "w",
            "&#7813;" => "w",
            "?" => "Y",
            "&#374;" => "Y",
            "?" => "Y",
            "&#7922;" => "Y",
            "&#7928;" => "Y",
            "&#7926;" => "Y",
            "&#7924;" => "Y",
            "?" => "y",
            "?" => "y",
            "&#375;" => "y",
            "&#7929;" => "y",
            "&#7925;" => "y",
            "&#7927;" => "y",
            "&#7923;" => "y",
            "&#377;" => "Z",
            "&#379;" => "Z",
            "?" => "Z",
            "&#378;" => "z",
            "&#380;" => "z",
            "?" => "z"
        );
        $str = strtr($str, $ch0);
        return $str;
    }

    /**
     *
     * @param type $string
     * @return type 
     */
    public static function add_slash(&$string) {
        $string = (substr($string, -1) == '/') ? $string : $string . '/';
        return $string;
    }

    /**
     *
     * @param type $string
     * @return type 
     */
    public static function add_brackets(&$string) {
        $string = str_replace(array("\'"), array("'"), $string);
        $string = str_replace(array("'"), array("\'"), $string);
        $string = "'$string'";
        return $string;
    }

    /**
     *
     * @param mixte $var
     * @return string 
     */
    public static function enclose_var($var) {
        if (is_null($var))
            $var = '';

        if (is_bool($var)) {
            if ($var == true)
                $var = 'true';
            if ($var == false)
                $var = 'false';
        }

        if (is_string($var) && !is_numeric($var)) {
            self::add_brackets($var);
        }
        return $var;
    }

    /**
     *
     * @param array $array
     * @return array of strings
     */
    public static function enclose_array_items(array $array) {
        $result = array();
        foreach ($array as $key => $value) {
            $result[$key] = self::enclose_var($value);
        }
        return $result;
    }

    /*     * ******************************************************************
      OUTPUTING */

// formatting
    /**
     *
     * @return type 
     */
    public static function format() {
        $args = func_get_args();
        $text = array_shift($args);
        $text = vsprintf($text, $args);
        return $text;
    }

    /*
     * CLIENT
     */

    /**
     * 
     */
    public static function get_client_desc($property = null) {
        if (!$property)
            return $_SERVER;
        else {
            switch ($property) {
                case '' : case '' : return $_SERVER[''];
                    break;
                case '' : case '' : return $_SERVER[''];
                    break;
                case '' : case '' : return $_SERVER[''];
                    break;
                case '' : case '' : return $_SERVER[''];
                    break;
                case '' : case '' : return $_SERVER[''];
                    break;
                case '' : case '' : return $_SERVER[''];
                    break;
                case '' : case '' : return $_SERVER[''];
                    break;
                case '' : case '' : return $_SERVER[''];
                    break;
                case '' : case '' : return $_SERVER[''];
                    break;
                default : return false;
            }
        }
    }

    /**
     *
     * @return string ip address 
     */
    public static function get_real_ip_address() {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    /**
     *
     * @param type $fieldName
     * @return type 
     */
    public static function format_content_field_name($fieldName) {
        $fieldName = str_replace('[]', '', $fieldName);
        $fieldName = str_replace('[', ' > ', $fieldName);
        $fieldName = str_replace(']', '', $fieldName);
        $fieldName = str_replace('_', ' ', $fieldName);
        return $fieldName;
    }

    /**
     * print_r with <pre> html tags
     * @param mixt $obj
     * @param boolean $print
     * @return string 
     */
    public static function printr($obj, $print = FALSE) {
        if ($print)
            echo '<pre class="printr">' . print_r($obj, 1) . '</pre>';
        else
            return '<pre class="printr">' . print_r($obj, 1) . '</pre>';
    }

    public static function fix($file, $line, $comment = '') {
        rv_set_message("to fix, $comment  <b>$file</b> $line", MSG_TEST);
    }

}

?>
