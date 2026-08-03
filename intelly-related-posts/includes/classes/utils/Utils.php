<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class IRP_Utils {

    function format($message, $v1=NULL, $v2=NULL, $v3=NULL, $v4=NULL, $v5=NULL) {
        if($v1 || $v2 || $v3 || $v4 || $v5) {
            $message=sprintf($message, $v1, $v2, $v3, $v4, $v5);
        }
        return $message;
    }
    function startsWith($haystack, $needle) {
        $result=FALSE;
        if (is_array($needle)) {
            foreach($needle as $w) {
                if ($this->startsWith($haystack, $w)) {
                    $result=TRUE;
                    break;
                }
            }
        } elseif($needle!='') {
            if(is_array($haystack)) {
                foreach($haystack as $h) {
                    if($this->startsWith($h, $needle)) {
                        $result=TRUE;
                        break;
                    }
                }
            } elseif($haystack!='') {
                $length = strlen($needle);
                $result = (substr($haystack, 0, $length) === $needle);
            }
        }
        return $result;
    }
    function endsWith($haystack, $needle) {
        $result=FALSE;
        if (is_array($needle)) {
            foreach($needle as $w) {
                if ($this->endsWith($haystack, $w)) {
                    $result=TRUE;
                    break;
                }
            }
        } elseif($needle!='') {
            if(is_array($haystack)) {
                foreach($haystack as $h) {
                    if($this->endsWith($h, $needle)) {
                        $result=TRUE;
                        break;
                    }
                }
            } elseif($haystack!='') {
                $length = strlen($needle);
                $start = $length * -1; //negative
                $result=(substr($haystack, $start) === $needle);
            }
        }
        return $result;
    }
    function substr($text, $start=0, $end=-1) {
        if($end<0) {
            $end=strlen($text);
        }
        $length=$end-$start;
        return substr($text, $start, $length);
    }

    //WOW! $end is passed as reference due to we can change it if we found \n character after
    //substring to avoid having these characters after or before
    function substrln($text, $start=0, &$end=-1) {
        if($end<0) {
            $end=strlen($text);
        }

        do {
            $loop=FALSE;
            $c=substr($text, $end, 1);
            if($c=="\n" || $c=="\r" || $c==".") {
                $end += 1;
                $loop=TRUE;
            }
        } while($loop);

        $length=$end-$start;
        return substr($text, $start, $length);
    }

    function toCommaArray($array, $isNumeric=TRUE, $isTrim=TRUE) {
        if(is_string($array)) {
            if(trim($array)=='') {
                $array=array();
            } else {
                $array=explode(',', $array);
            }
        } elseif(is_numeric($array)) {
            $array=array($array);
        }
        if(!is_array($array)) {
            $array=array();
        }
        for($i=0; $i<count($array); $i++) {
            if($isTrim) {
                $array[$i]=trim($array[$i]);
            }
            if($isNumeric) {
                $array[$i]=floatval($array[$i]);
            }
        }
        return $array;
    }
    //verifica se il parametro needle è un elemento dell'array haystack
    //se il parametro needle è a sua volta un array verifica che almeno un elemento
    //sia contenuto all'interno dell'array haystack
    function inArray($needle, $haystack) {
        if (is_string($haystack)) {
            //from string to numeric array
            $temp = explode(',', $haystack);
            $haystack = array();
            foreach ($temp as $v) {
                $v = trim($v);
                $v = intval($v);
                if ($v > 0) {
                    $haystack[] = $v;
                }
            }
        }

        $result = FALSE;
        foreach ($haystack as $v) {
            $v = intval($v);
            //if one element of the array have -1 value means i select "all" option
            if ($v < 0) {
                $result = TRUE;
                break;
            }
        }

        if ($result) {
            return TRUE;
        }

        $result = FALSE;
        if (is_array($needle)) {
            foreach ($needle as $v) {
                $v = trim($v);
                $v = intval($v);
                if (in_array($v, $haystack)) {
                    $result = TRUE;
                    break;
                }
            }
        } else {
            //built-in comparison
            $result = in_array($needle, $haystack);
        }
        return $result;
    }

    function is($name, $compare, $default='', $ignoreCase=TRUE) {
        $what=$this->qs($name, $default);
        $result=FALSE;
        if(is_string($compare)) {
            $compare=explode(',', $compare);
        }
        if($ignoreCase){
            $what=strtolower($what);
        }

        foreach($compare as $v) {
            if($ignoreCase){
                $v=strtolower($v);
            }
            if($what==$v) {
                $result=TRUE;
                break;
            }
        }
        return $result;
    }

    public function twitter($name) {
        // Self-contained X (formerly Twitter) follow link. The old markup relied on
        // Twitter's platform.twitter.com/widgets.js to turn a `.twitter-follow-button`
        // anchor into a logo button; that widget was retired after the X rebrand, so it
        // rendered as bare text with no logo. Inline the X mark as SVG instead — no
        // external script, works offline, and shows the correct current brand.
        // Styling lives in the .irp-x-follow rules in assets/css/style.css (enqueued on
        // the admin screens that call this), which render it as a compact X-brand pill.
        ?>
        <a href="https://x.com/<?php echo esc_attr( $name ); ?>" target="_blank" rel="noopener noreferrer" class="irp-x-follow">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true" focusable="false">
                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
            </svg>
            <span>Follow @<?php echo esc_html( $name ); ?></span>
        </a>
    <?php
    }

    function aqs($prefix, $defaults=array()) {
        global $irp;

        $removePrefix=TRUE;
        $args=array();
        // phpcs:ignore WordPress.Security.NonceVerification -- generic request reader; every value is passed through sanitize_text_field() below and CSRF is verified by the caller that acts on the data (settings save, metabox save, action dispatcher).
        $array=$this->merge(TRUE, $_POST, $_GET);
        foreach($array as $k=>$v) {
            if($this->startsWith($k, $prefix)) {
                if($removePrefix) {
                    $k=substr($k, strlen($prefix));
                }
                $args[$k]=sanitize_text_field($v);
            }
        }
        $args=$irp->Utils->parseArgs($args, $defaults);
        return $args;
    }
    function iqs($name, $default = 0) {
        return intval($this->qs($name, $default));
    }
    //per ottenere un campo dal $_GET oppure dal $_POST
    function qs($name, $default = '')
    {
        $result = $default;

        // phpcs:disable WordPress.Security.NonceVerification -- generic request reader; the value is sanitised here and CSRF is verified by the caller that acts on it.
        if (isset($_GET[$name])) {
            $result = sanitize_text_field( $_GET[$name] );
        } else {
            if (isset($_POST[$name])) {
                $result = sanitize_text_field( $_POST[$name] );
            }
        }
        // phpcs:enable WordPress.Security.NonceVerification

        if (is_string($result)) {
            // No urldecode() here: PHP already populates $_GET/$_POST decoded,
            // and decoding again *after* sanitize_text_field() could re-introduce
            // characters the sanitizer removed.
            $result = trim($result);
        }

        return wp_kses( $result, $this->kses_allowed_html(), $this->kses_allowed_protocols() );
    }

    function sanitizeMargin($name, $default = '')
    {
        $result = $default;
        // phpcs:disable WordPress.Security.NonceVerification -- generic request reader; the value is sanitised here and reduced to CSS unit characters below, and CSRF is verified by the caller that acts on it.
        if (isset($_GET[$name])) {
            $result = sanitize_text_field( $_GET[$name] );
        } else {
            if (isset($_POST[$name])) {
                $result = sanitize_text_field ( $_POST[$name] );
            }
        }
        // phpcs:enable WordPress.Security.NonceVerification

        // Define a regular expression pattern to match invalid characters
        $pattern = '/[^0-9pxem%rvwh]/';

        $sanitizedString = preg_replace($pattern, '', $result);

        return $sanitizedString;
    }

    function query($query, $args = NULL) {
        global $irp;

        $defaults = array('post_type' => '', 'all' => FALSE, 'select' => FALSE);
        $args = wp_parse_args($args, $defaults);

        $result = $irp->Options->getCache('Query', $query . '_' . $args['post_type']);
        if (!is_array($result) || count($result) == 0) {
            $q = NULL;
            $id = 'ID';
            $name = 'post_title';
            $function='';
            switch ($query) {
                case IRP_QUERY_POSTS_OF_TYPE:
                    $options = array('posts_per_page' => -1, 'post_type' => $args['post_type']);
                    $q = get_posts($options);
                    $function='get_permalink';
                    break;
                case IRP_QUERY_CATEGORIES:
                    $options = array('posts_per_page' => -1);
                    $q = get_categories($options);
                    $id = 'cat_ID';
                    $name = 'cat_name';
                    $function='get_category_link';
                    break;
                case IRP_QUERY_TAGS:
                    $q = get_tags();
                    $id = 'term_id';
                    $name = 'name';
                    $function='get_tag_link';
                    break;
            }

            $result = array();
            if ($q) {
                foreach ($q as $v) {
                    $result[] = array('id' => $v->$id, 'name' => $v->$name);
                }
            } elseif ($query == IRP_QUERY_POST_TYPES) {
                $options = array('public' => true, '_builtin' => false, 'exclude_from_search' => false);
                $q = get_post_types($options, 'names');
                $q = array_merge($q, array('post'));
                sort($q);
                foreach ($q as $v) {
                    $result[] = array('id' => $v, 'name' => $v);
                }
            }

            if($function!='' && function_exists($function)) {
                for($i=0; $i<count($result); $i++) {
                    $v=$result[$i];
                    $v['url']=call_user_func_array($function, array($v['id']));
                    $result[$i]=$v;
                }
            }
            $irp->Options->setCache('Query', $query . '_' . $args['post_type'], $result);
        }

        if ($args['all']) {
            $first = array();
            $first[] = array('id' => -1, 'name' => '[' . $irp->Lang->L('All') . ']', 'url'=>'');
            $result = array_merge($first, $result);
        }
        if ($args['select']) {
            $first = array();
            $first[] = array('id' => 0, 'name' => '[' . $irp->Lang->L('Select') . ']', 'url'=>'');
            $result = array_merge($first, $result);
        }

        return $result;
    }

    function remotePost($action, $data = '') {
        global $irp;

        // (Removed the hard-coded 'secret' => 'WYSIWYG' field: it was committed
        // in plaintext, so it provided no authentication value.)
        $response = wp_remote_post(IRP_INTELLYWP_ENDPOINT.'?iwpm_action=' . $action, array(
            'method' => 'POST'
            , 'timeout' => 2
            , 'redirection' => 5
            , 'httpversion' => '1.1'
            , 'blocking' => TRUE
            , 'body' => $data
            , 'user-agent' => 'IRPP/' . IRP_PLUGIN_VERSION . '; ' . get_bloginfo('url')
        ));
        $data = json_decode(wp_remote_retrieve_body($response), TRUE);
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200
            || !isset($data['success']) || !$data['success']
        ) {
            $irp->Log->error('ERRORS SENDING REMOTE-POST ACTION=%s DUE TO REASON=%s', $action, $response);
            $data = FALSE;
        } else {
            $irp->Log->debug('SUCCESSFULLY SENT REMOTE-POST ACTION=%s RESPONSE=%s', $action, $data);
        }
        return $data;
    }

    function shortcodeAtts($defaults, $atts) {
        if(!is_array($atts)) {
            $atts=array();
        }

        $array=array();
        foreach($defaults as $k=>$v) {
            if (array_key_exists($k, $atts) ) {
                $array[$k] = $atts[$k];
            } elseif (array_key_exists(strtolower($k), $atts) ) {
                $array[$k] = $atts[strtolower($k)];
            } else {
                $array[$k] = $v;
            }
        }
        return $array;
    }

    //wp_parse_args with null correction
    function parseArgs($args, $defaults) {
        if (is_null($args) || !is_array($args)) {
            $args = array();
        }
        foreach ($args as $k => $v) {
            if (is_null($args[$k])) {
                //so can take the default value
                unset($args[$k]);
            } elseif (is_string($args[$k]) && $args[$k] == '' && isset($defaults[$k]) && is_array($defaults[$k])) {
                //a very strange case, i have a blank string for rappresenting an empty array
                unset($args[$k]);
            }
        }
        foreach ($args as &$value) {
            $value = wp_kses( $value, $this->kses_allowed_html(), $this->kses_allowed_protocols() );
        }
        $result = wp_parse_args($args, $defaults);
        return $result;
    }

    //hosts this plugin is allowed to send the browser to, on top of the site's own
    function allowedRedirectHosts($hosts) {
        $urls = array(IRP_TAB_DOCS_URI, IRP_INTELLYWP_SITE, IRP_PAGE_WORDPRESS);
        foreach($urls as $url) {
            $host = wp_parse_url($url, PHP_URL_HOST);
            if($host) {
                $hosts[] = $host;
            }
        }
        return $hosts;
    }

    function redirect($location) {
        if(!headers_sent()) {
            //wp_safe_redirect() keeps the redirect on this site; the plugin's own
            //destinations (the docs site) are allowed through the filter below
            add_filter('allowed_redirect_hosts', array($this, 'allowedRedirectHosts'));
            wp_safe_redirect($location);
            exit();
        }
        ?>
        <div id="irpRedirect" href="<?php echo esc_url( $location ); ?>"></div>
		<?php
        exit();
    }

    //return the element inside array with the specified key
    function getArrayValue($key, $array, $value='') {
        $result=FALSE;
        if (isset($array[$key])) {
            $result=$array[$key];
            $result['name']=$key;
        }
        if($result!==FALSE && $value!='') {
            if(isset($result[$value])) {
                $result=$result[$value];
            }
        }
        return $result;
    }

    var $_sortField;
    var $_ignoreCase;
    function aksort(&$array, $sortField='name', $ignoreCase=TRUE) {
        $this->_sortField=$sortField;
        $this->_ignoreCase=$ignoreCase;
        usort($array, array($this, "aksortCompare"));
    }
    //not thread-safe!
    private function aksortCompare($a, $b) {
        if ($a===$b || $a==$b) {
            return 0;
        }

        $result=0;
        $a=$a[$this->_sortField];
        $b=$b[$this->_sortField];
        if(is_numeric($a) && is_numeric($b)) {
            $result=($a < $b) ? -1 : 1;
        } else {
            $a.='';
            $b.='';
            if($this->_ignoreCase) {
                $result=strcasecmp($a, $b);
            } else {
                $result=strcmp($a.'', $b);
            }
        }
        return $result;
    }

    function printScriptCss() {
        global $irp;
        $uri=get_bloginfo('wpurl');
        $irp->Tabs->enqueueScripts();
        //wp_enqueue_style('buttons', $uri.'/wp-includes/css/buttons.min.css');
        //wp_enqueue_style('editor', $uri.'/wp-includes/css/editor.min.css');
        //wp_enqueue_style('jquery-ui-dialog', $uri.'/wp-includes/css/jquery-ui-dialog.min.css');
        $styles='dashicons,admin-bar,buttons,media-views,wp-admin,wp-auth-check,wp-color-picker';
        $styles=explode(',', $styles);
        foreach($styles as $v) {
            wp_enqueue_style($v);
        }

        remove_all_actions('wp_print_scripts');
        print_head_scripts();
        print_admin_styles();
    }

    function load_related_box_script() {
        global $irp;
        $defaults=$irp->HtmlTemplate->getDefaults();
        $defs = array();
        foreach($defaults as $k=>$v) {
            $buffer='';
            foreach($v as $kk=>$vv) {
                if($buffer!='') {
                    $buffer.=', ';
                }
    
                if(stripos($kk, 'label')!==FALSE) {
                    $vv=$irp->Lang->L('Settings.Color.'.$vv);
                } elseif(stripos($kk, 'color')!==FALSE) {
                    $vv=$irp->Options->getColor($vv);
                }
    
                $defs[$k][$kk] = $vv;
            }
        }
    
        wp_enqueue_script( 'irp_settings', IRP_PLUGIN_ASSETS . 'js/settings.js', array('jquery'), '2.0' );
        wp_add_inline_script( 'irp_settings', 'const settings_data = ' . wp_json_encode( $defs ) . ';', 'before' );
        // Nonce consumed by the ui_box_preview do_action dispatcher case.
        wp_localize_script( 'irp_settings', 'irp_settings_ajax', array( 'nonce' => wp_create_nonce( 'irp_do_action' ) ) );
    }

    public function merge($isAssociative, $a1, $a2=NULL, $a3=NULL, $a4=NULL, $a5=NULL) {
        $result=array();
        if($isAssociative) {
            $array=array($a1, $a2, $a3, $a4, $a5);
            foreach($array as $a) {
                if(!is_array($a)) {
                    continue;
                }

                foreach($a as $k=>$v) {
                    if(!isset($result[$k])) {
                        $result[$k]=$v;
                    }
                }
            }
        } else {
            $result=array_merge($a1, $a2, $a3, $a4, $a5);
        }
        return $result;
    }
    function get($array, $name, $default='') {
        $result=$default;
        if(isset($array[$name])) {
            $result=$array[$name];
        }
        return wp_kses( $result, $this->kses_allowed_html(), $this->kses_allowed_protocols() );
    }
    function geti($array, $name, $default='') {
        $result=$default;
        $name=strtolower($name);
        foreach($array as $k=>$v) {
            if(strtolower($k)==$name) {
                $result=$v;
                break;
            }
        }
        if(isset($array[$name])) {
            $result=$array[$name];
        }
        return $result;
    }
    function iget($array, $name, $default='') {
        return intval($this->get($array, $name, $default));
    }
    function isTrue($value) {
        $result=FALSE;
        if(is_bool($value)) {
            $result=(bool)$value;
        } elseif(is_numeric($value)) {
            $result=floatval($value)>0;
        } elseif(is_string($value)) {
            $result=strtolower($value);
            if($result=='ok' || $result=='yes' || $result=='true') {
                $result=TRUE;
            }
        }
        return $result;
    }
    function trimCode($code) {
        $code=str_replace("\t", "", $code);
        $code=str_replace("\r", "", $code);
        $code=str_replace("\n", "", $code);
        while(strpos($code, "  ")!==FALSE) {
            $code=str_replace("  ", " ", $code);
        }
        $code=str_replace("> <", "><", $code);
        $code=trim($code);
        return $code;
    }
    function getUUID($options) {
        $buffer='';
        if(is_string($options)) {
            $buffer=$options;
        } elseif(is_array($options)) {
            foreach($options as $k=>$v) {
                $buffer.=', '.$k.'='.$v;
            }
        }
        if($buffer!='') {
            $buffer='u'.md5($buffer);
        }
        return $buffer;
    }

    function isAdminUser() {
        //https://wordpress.org/support/topic/how-to-check-admin-right-without-include-pluggablephp
        // Guard on wp_get_current_user(), NOT current_user_can(): the latter is
        // defined early (capabilities.php) but internally calls the former, a
        // pluggable function loaded later. Guarding on current_user_can() would
        // pass, skip the require, then fatal on the undefined wp_get_current_user().
        if (!function_exists('wp_get_current_user')) {
            require_once ABSPATH . 'wp-includes/pluggable.php';
        }
        return current_user_can('manage_options');
    }
    function isPluginPage() {
        $page=$this->qs('page');
        $result=(stripos($page, IRP_PLUGIN_SLUG)!==FALSE);
        return $result;
    }
    // Allowlist for wp_kses(). Deliberately excludes <script>, <style> and any
    // inline "style" attribute so that request-derived data laundered through
    // this list cannot smuggle script/CSS injection. The inline formatting tags
    // (b/i/em/strong/br) are the ones the plugin's own UI copy in Lang.txt uses.
    function kses_allowed_html() {
        return array(
            'a'      => array( 'href' => array(), 'target' => array(), 'rel' => array(), 'class' => array() ),
            'div'    => array( 'class' => array() ),
            'p'      => array( 'class' => array() ),
            'img'    => array( 'src' => array() ),
            'span'   => array( 'class' => array() ),
            'b'      => array(),
            'i'      => array(),
            'em'     => array(),
            'strong' => array(),
            'br'     => array(),
        );
    }

    // Protocols permitted in URLs that pass through wp_kses(). Only real,
    // navigable schemes — never "javascript:".
    function kses_allowed_protocols() {
        return array( 'http', 'https', 'mailto' );
    }

    // Allowlist used ONLY by the admin box-preview (activate_plugins-gated).
    // Unlike kses_allowed_html() it permits <style> and inline "style"
    // attributes, because the preview embeds the box's own inline stylesheet.
    // It still refuses <script>. Never use this on request-derived data.
    function kses_allowed_html_preview() {
        return array(
            'style' => array(),
            'a'     => array( 'href' => array(), 'target' => array(), 'rel' => array(), 'class' => array(), 'style' => array() ),
            'div'   => array( 'class' => array(), 'style' => array() ),
            'p'     => array( 'class' => array(), 'style' => array() ),
            'span'  => array( 'class' => array(), 'style' => array() ),
            'img'   => array( 'src' => array(), 'class' => array(), 'style' => array(), 'width' => array(), 'height' => array(), 'alt' => array() ),
        );
    }
}
