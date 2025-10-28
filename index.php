<?php
/**
 * index.php represents the hub of the Zen Cart MVC system
 * 
 * Overview of flow
 * <ul>
 * <li>Load application_top.php - see {@tutorial initsystem}</li>
 * <li>Set main language directory based on $_SESSION['language']</li>
 * <li>Load all *header_php.php files from includes/modules/pages/PAGE_NAME/</li>
 * <li>Load html_header.php (this is a common template file)</li>
 * <li>Load main_template_vars.php (this is a common template file)</li>
 * <li>Load on_load scripts (page based and site wide)</li>
 * <li>Load tpl_main_page.php (this is a common template file)</li>
 * <li>Load application_bottom.php</li>
 * </ul>
 *
 * @package general
 * @copyright Copyright 2003-2005 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: index.php 2942 2006-02-02 04:41:23Z drbyte $
 */
 $undefined_constants_array = '';
function defset($constant) {
    if (defined($constant)) {
        if ($constant != '') {
            return true;
        }
    }
    return false;
}
include_once('includes/configure.php');




timer_log_configure('index_start');
/**
 * Load common library stuff
 */
require('includes/application_top.php');
timer_log_configure('after application_top');
$language_page_directory = DIR_WS_LANGUAGES . $_SESSION['language'] . '/';
$directory_array = $template->get_template_part($code_page_directory, '/^header_php/');

foreach ($directory_array as $value) {
/**
 * We now load header code for a given page.
 * Page code is stored in includes/modules/pages/PAGE_NAME/directory
 * 'header_php.php' files in that directory are loaded now.
 */
    timer_log_configure('before ' . $code_page_directory . '/' . $value);
    require($code_page_directory . '/' . $value);
    timer_log_configure('after ' . $code_page_directory . '/' . $value);
}
/**
 * We now load the html_header.php file. This file contains code that would appear within the HTML <head></head> code
 * it is overridable on a template and page basis.
 * In that a custom template can define its own common/html_header.php file
 */



//settings for cahce is made in init_solr.php
if ($RI_CJLoader->get('status')) {
    $directory_array = $template->get_template_part(DIR_WS_TEMPLATE . 'auto_loaders', '/^loader_/', '.php');

    $loaders_check = $RI_CJLoader->get('loaders');
    if ($loaders_check == '*' || count($loaders_check) > 0) {
        while (list ($key, $value) = each($directory_array)) {
            /**
            * include content from all site-wide loader_*.php files from includes/templates/YOURTEMPLATE/jscript/auto_loaders, alphabetically.
            */
            if ($loaders_check == '*' || in_array($value, $loaders_check))
                require(DIR_WS_TEMPLATE . 'auto_loaders' . '/' . $value);
        }
    }

    $RI_CJLoader->loadCssJsFiles();
    $files = $RI_CJLoader->processCssJsFiles();
    foreach ($files['css'] as $file)
        if ($file['include']) {
            include($file['string']);
        } else {
            $css_files .= $file['string'];
        }


    if ($ssheet != '') {
        $css_files .= '<link rel="stylesheet" type="text/css" href="/includes/templates/template_ai/css/s_' . $ssheet . '.css?' . time() . '" />';
        if ($ssheet == 'blog') {
            $css_files .= '<link rel="stylesheet" type="text/css" href="/includes/templates/template_ai/css/s_' . $ssheet . '_specific.css?' . time() . '" />';
        } else {
            $css_files .= '<link rel="stylesheet" type="text/css" href="/includes/templates/template_ai/css/s_' . $ssheet . '_' . DOMAIN . '.css?' . time() . '" />';
        }
        $css_files .= '<link href="//fonts.googleapis.com/css?family=Lato:400,900|Droid+Serif:400,400italic" rel="stylesheet" type="text/css">';
    }


    /*$jscripts_from_html_header_and_page .= '<script type="text/javascript" src="' . zen_get_image_cdn('includes/templates/template_ai/jscript/jquery/jquery.min.use.js') . '"></script>';
    $jscripts_from_html_header_and_page .= '<script type="text/javascript" src="' . zen_get_image_cdn('includes/templates/template_ai/jscript/jquery/jquery-ui.min.use.js') . '"></script>';*/
    //$jscripts_from_html_header_and_page .= '<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>';
    //$jscripts_from_html_header_and_page .= '<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.24/jquery-ui.min.js"></script>';
    $jscripts_from_html_header_and_page .= '<script type="text/javascript" src="/includes/templates/template_ai/jscript/jquery/jquery.min.js"></script>';
    $jscripts_from_html_header_and_page .= '<script type="text/javascript" src="/includes/templates/template_ai/jscript/jquery/jquery-ui.min.js"></script>';

    foreach ($files['js'] as $file) {
        if ($file['include']) {
            //include($file['string']);
            $jscripts_includes_array_from_html_header[] = $file['string'];
        } else {
            $jscripts_from_html_header_and_page .= $file['string'];
        }
    }



    //}*/

}

    // FIX transport old cookies
    $consent_storage_json = zen_getcookie('consent_storage');
    $consent_storage = json_decode($consent_storage_json,1);

    $allow_rm = zen_getcookie('allow_rm');
    if($allow_rm != '' && $consent_storage['allow_rm'] != $allow_rm){
        $consent_storage['allow_rm'] = $allow_rm;
        $consent_storage_changed = true;
    }

    $cookieConsentSZ = zen_getcookie('cookieConsentSZ');
    if($cookieConsentSZ != '' && $consent_storage['cookieConsentSZ'] != $cookieConsentSZ){
        $consent_storage['cookieConsentSZ'] = $cookieConsentSZ;
        $consent_storage_changed = true;
    }

    $integterms = zen_getcookie('integterms');
    if($integterms != '' && $consent_storage['integterms'] != $integterms){
        $consent_storage['integterms'] = $integterms;
        $consent_storage_changed = true;
    }
    if($consent_storage['integterms'] == 1 && (!isset($consent_storage['analytics_storage']))){
        custom_log('cookies_log', json_encode($consent_storage) .  ' ' . $_SERVER['HTTP_USER_AGENT']);
        if($consent_storage['allow_rm'] == 'yes'){
            $consent_storage['allow_rm'] = 'enable';
            $consent_storage['functionality_storage'] = 'enable';
            $consent_storage['security_storage'] = 'enable';
            $consent_storage['personalization_storage'] = 'enable';
            $consent_storage['analytics_storage'] = 'enable';

            custom_log('cookies_log', ' SSS SET ' . json_encode($consent_storage) .  ' ' . $_SERVER['HTTP_USER_AGENT']);
            $consent_storage_changed = true;
        }
        //$consent_storage['integterms'] = $integterms;
    }
    $set_cookie_js = '';
    if($consent_storage_changed){
        //zen_setcookie("consent_storage", json_encode($consent_storage), time() + (60 * 60 * 24 * 30));
        $set_cookie_js = "setCookie('consent_storage', '" . json_encode($consent_storage) . "', (365));";
        if(zen_getcookie('cookie_test') == ''){
            //e_log('From: ' . $consent_storage_json . ' To: ' . json_encode($consent_storage));
            //e_log( $_SERVER);
        }
        
    }

    $known_cookies['zenAdminID'] = '';
    $known_cookies['admq'] = '';

    $known_cookies['nav'] = '';
    $known_cookies['consent_storage'] = '';
    $known_cookies['zenid'] = '';

    $known_cookies['_gcl'] = '';
    $known_cookies['_gac_'] = '';
    $known_cookies['GA4_ga'] = '';
    $known_cookies['UA_ga'] = '';
    $known_cookies['sa'] = '';
    $known_cookies['_gat'] = '';
    $known_cookies['_gali'] = '';
    $known_cookies['aref'] = '';
    $known_cookies['AW_au'] = '';
    $known_cookies['_uetsid'] = '';
    $known_cookies['_uetvid'] = '';
    $known_cookies['_uetmsclkid'] = '';
    $known_cookies['__utma'] = '';
    $known_cookies['__utmb'] = '';
    $known_cookies['__utmc'] = '';
    $known_cookies['__utmt'] = '';
    $known_cookies['__utmz'] = '';
    $known_cookies['__utmv'] = '';
    $known_cookies['_gaexp'] = '';
    $known_cookies['_fbp'] = '';
    $known_cookies['_ga'] = '';
    $known_cookies['cart'] = '';
    $known_cookies['_clck'] = '';
    $known_cookies['integterms'] = '';
    $known_cookies['allow_rm'] = '';
    $known_cookies['AW_aw'] = '';
    $known_cookies['source'] = '';
    $known_cookies['trackID'] = '';
    $known_cookies['sa'] = '';

    $known_cookies['_pin_unauth'] = '';
    $known_cookies['_derived_epik'] = '';

    $known_cookies['_clsk'] = ''; // f.clarity.ms/collect
    
    /*$known_cookies[''] = '';
    $known_cookies[''] = '';*/

    
    foreach($_COOKIE as $cookie_name => $cookie_value){
        $skip_cookie = false;
        foreach ($known_cookies as $known_cookie_name => $dddd) {
            $strlen = strlen($known_cookie_name);
            if($known_cookie_name == substr($cookie_name, 0, $strlen)){
                $skip_cookie = true;
            }else{
                //custom_log('cookies_log', 'NO Cookie: ' . $known_cookie_name . ' != ' . substr($cookie_name, 0, $strlen));// .  ' ' . $_SERVER['HTTP_USER_AGENT']);
            }
        }
        if(!$skip_cookie){
            $remove_cookies['fornax_anonymousId'] = ''; // bigcommerce
            $remove_cookies['_hjSessionUser_3468591'] = ''; // hotjar
            $remove_cookies['cto_bundle'] = ''; // criteo
            $remove_cookies['tracking-preferences'] = ''; // 
            $remove_cookies['datadome'] = ''; // datadome

            $remove_cookies['hello_retail_id'] = ''; // hello_retail_id

            $remove_cookies['hello_retail_id'] = '';
            $remove_cookies['IR_PI'] = '';
            $remove_cookies['nostojs'] = '';
            $remove_cookies['__atuvc'] = '';
            $remove_cookies['2c_cId'] = '';

            $remove_cookies['commentuser'] = '';

            $remove_cookies['CKFinder_Path'] = '';
            $remove_cookies['65e842de6743f'] = '';
            $remove_cookies['sg_cookies'] = '';
            $remove_cookies['cookiebanner'] = '';
            
            
            

            if(isset($remove_cookies[$cookie_name])){
                setcookie($cookie_name, "", time()-3600);
            }
            custom_log('cookies_log', 'Unknown Cookie: ' . $cookie_name . '    value:' . $cookie_value);// .  ' ' . $_SERVER['HTTP_USER_AGENT']);
        }
        
    }



    // ENd fix transport old cookies


?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml"
xmlns:og="http://opengraphprotocol.org/schema/"
xmlns:fb="http://www.facebook.com/2008/fbml" <?php echo HTML_PARAMS; ?>>
<head>
<?php
if (defset('GOOGLE_WEBMASTER_SITE_VERIFICATION')) {
    echo GOOGLE_WEBMASTER_SITE_VERIFICATION;
}
if ($_SERVER['SERVER_NAME'] != $allowed_hosts[$_SERVER['SERVER_NAME']]) {
    echo '<base href="' . HTTPS_SERVER . '">' . "\n";
}


timer_log_configure('after RI_CJLoader');
$SimpleCache_replace_array['##SESSION_LISTING_VIEW##'] = ' session_set';
if($_SESSION['products_listing_view'] != ''){
    $SimpleCache_replace_array['##SESSION_LISTING_VIEW##'] = '-session_' . $_SESSION['products_listing_view'];
} 
if($_SESSION['products_listing_view'] == 'list_view'){
    $SimpleCache_replace_array['##SESSION_LISTING_VIEW##'] .= ' listing_list_view';
} 
$SimpleCache_replace_array['##CSS_FILES##'] = $css_files;
$SimpleCache_replace_array['##TO_DESKTOP_SITE##'] = '<!--TO_DESKTOP_SITE-->';
$SimpleCache_replace_array['##VIEWPORT##'] = '<!--VIEWPORT-->';

$SimpleCache_replace_array['##COOKIE_CONSENT##'] = '<!--COOKIE_CONSENT-->';


/*
function isBotCheck() {
    if ($_SERVER['REMOTE_ADDR'] == '153.92.126.89' ){
        //return true;
    }

    return (
    isset($_SERVER['HTTP_USER_AGENT'])
    && preg_match('/(googlebot\/|Googlebot-Mobile|Storebot-Google|ThecaBot|amazonbot|GPTBot|ClaudeBot|Googlebot-Image|AdsBot-Google|Bytespider|AdsBot-Google-Mobile|adsbot|Wget|Google favicon|GeedoBot|ImagesiftBot|SeekportBot|Mediapartners-Google|uptimerobot|AlphaBot|Pinterestbot|bingbot|petalbot|Pingdom.com_bot|KlarnaBot|Slackbot|SEOkicks|zoominfobot|FeedBot|slurp|java|wget|curl|Commons-HttpClient|Python-urllib|libwww|httpunit|nutch|phpcrawl|msnbot|jyxobot|TrueClicks|LinkTester|babbar.tech|FAST-WebCrawler|FAST Enterprise Crawler|biglotron|teoma|convera|seekbot|gigablast|exabot|ngbot|ia_archiver|GingerCrawler|webmon |httrack|webcrawler|grub.org|UsineNouvelleCrawler|antibot|netresearchserver|speedy|fluffy|bibnum.bnf|findlink|msrbot|panscient|yacybot|AISearchBot|IOI|ips-agent|tagoobot|MJ12bot|dotbot|woriobot|yanga|buzzbot|mlbot|yandexbot|purebot|Linguee Bot|Voyager|CyberPatrol|voilabot|baiduspider|citeseerxbot|spbot|twengabot|postrank|turnitinbot|scribdbot|page2rss|sitebot|linkdex|Adidxbot|blekkobot|ezooms|dotbot|Mail.RU_Bot|discobot|heritrix|findthatfile|europarchive.org|NerdByNature.Bot|sistrix crawler|ahrefsbot|Aboundex|domaincrawler|wbsearchbot|summify|ccbot|edisterbot|seznambot|ec2linkfinder|gslfbot|aihitbot|intelium_bot|facebookexternalhit|yeti|RetrevoPageAnalyzer|lb-spider|sogou|lssbot|careerbot|wotbox|wocbot|ichiro|DuckDuckBot|lssrocketcrawler|drupact|webcompanycrawler|acoonbot|openindexspider|gnam gnam spider|web-archive-net.com.bot|backlinkcrawler|coccoc|integromedb|content crawler spider|toplistbot|seokicks-robot|it2media-domain-crawler|ip-web-crawler.com|siteexplorer.info|elisabot|proximic|changedetection|blexbot|arabot|WeSEE:Search|niki-bot|CrystalSemanticsBot|rogerbot|360Spider|psbot|InterfaxScanBot|Lipperhey SEO Service|CC Metadata Scaper|g00g1e.net|GrapeshotCrawler|urlappendbot|brainobot|fr-crawler|binlar|SimpleCrawler|Livelapbot|Twitterbot|cXensebot|smtbot|bnf.fr_bot|A6-Indexer|ADmantX|Facebot|Twitterbot|OrangeBot|memorybot|AdvBot|MegaIndex|SemanticScholarBot|ltx71|nerdybot|xovibot|BUbiNG|Qwantify|archive.org_bot|Applebot|TweetmemeBot|crawler4j|findxbot|DataForSeoBot|Snap URL Preview Service|SemrushBot|yoozBot|lipperhey|y!j-asr|Domain Re-Animator Bot|AddThis)/i', $_SERVER['HTTP_USER_AGENT'])
  );
}
$isbot = isBotCheck();
*/

   if ($consent_storage['integterms'] != '1' && !$isbot) {
        require('includes/modules/template_ai/cookie_settings.php');
        $SimpleCache_replace_array['##COOKIE_CONSENT##'] = $COOKIE_SETTINGS_HTML;
        define('SHOW_COOKIE_POPUP', 'yes');
    } else{
        define('SHOW_COOKIE_POPUP', 'no');
    }

    if($isbot) {
        custom_log('bot_visits', DOMAIN .  $_SERVER['REQUEST_URI'] . ' Agent:' . $_SERVER['HTTP_USER_AGENT'] ); 
    }elseif($isprefetch) {
        custom_log('bot_visits', '$isprefetch: ' . DOMAIN .  $_SERVER['REQUEST_URI'] . ' Agent:' . $_SERVER['HTTP_USER_AGENT'] ); 
    }else{
        custom_log('page_visits', DOMAIN . $_SERVER['REQUEST_URI'] . ' From:' . $_SERVER['HTTP_REFERER'] . ' Agent:' . $_SERVER['HTTP_USER_AGENT'] ); 
        if(strpos($_SERVER['REQUEST_URI'], 'advanced_search')){
            custom_log('page_visits', $_SERVER); 
        }
    }
    if($_GET['aref'] != ''){

        $message = '';
        $message_end = '';
        $split_aref = explode('_', $_GET['aref']);
        foreach($split_aref as $pair){
            $split_pair = explode('-', $pair);
            $aref_data_arr[$split_pair[0]] = $split_pair[1];
            $message = $aref_data_arr['ref'];

            $message_end .=  ' ' .$pair;
            if($split_pair[0] == 'kw'){
                $message = 'KW: ' . $split_pair[1];
            }
        }
        custom_log('aref_traffic', $message . ' ' . DOMAIN . $_SERVER['REQUEST_URI'] . ' From:' . $_SERVER['HTTP_REFERER'] . ' ' .$message_end);


        custom_log('google_traffic', $message . ' ' . DOMAIN . $_SERVER['REQUEST_URI'] . ' From:' . $_SERVER['HTTP_REFERER'] . ' ' .$message_end);
    }


if ($_GET['utm_source'] == 'referral' || $_GET['utm_medium'] == '4242' ) {
    custom_log('referral_4242', 'SERVER:' . print_r($_SERVER, 1) . ' _SESSION:' . print_r($_SESSION, 1) . ' _GET:' . print_r($_GET, 1));
}

if ($_SESSION['cookie_settype'] == 'new') {
?>



<?php
}else{
?>




<?php    
}

unset($_SESSION['force_full_size_view']);
$SimpleCache_replace_array['##VIEWPORT##'] = '<meta name="viewport" content="width=device-width, initial-scale=1"/>';
/*if ($_SESSION['force_full_size_view'] != 'yes') {
    $SimpleCache_replace_array['##TO_DESKTOP_SITE##'] = '<div class="choose_full_size_site">' . TEXT_TO_DESKTOP_SITE . ' ></div>';
    $SimpleCache_replace_array['##VIEWPORT##'] = '<meta name="viewport" content="width=device-width, initial-scale=1"/>';
}
$SimpleCache_replace_array['##TO_MOBILE_SITE##'] = '<!--TO_MOBILE_SITE-->';
if ($_SESSION['force_full_size_view'] == 'yes') {
    $SimpleCache_replace_array['##TO_MOBILE_SITE##'] = '<div class="choose_mobile_site">' . TEXT_TO_MOBILE_SITE . ' ></div>';
}*/



$google_analytics_template_dir = 'includes/templates/template_ai/google_analytics';
$SimpleCache_replace_array['##ANALYTICS_HTML##'] = '<!--ANALYTICS_HTML-->';
$SimpleCache_replace_array['##GTAG##'] = '<!--GTAG-->';

if($_SERVER['REMOTE_ADDR'] == '153.92.126.89dd'){
    //require($google_analytics_template_dir . '/gtag.php');
    //$SimpleCache_replace_array['##GTAG##'] = $gtag_html;
}else{

  //if (!isset($_COOKIE['zenAdminID']) && !isset($_COOKIE['admq']) && $current_page_base != 'popup_questionaire' && $current_page_base != 'popup_image' && $current_page_base != 'popup_image_additional' && TEST_ENVIRONMENT !== 'true') {
    if (zen_getcookie('zenAdminID') == '' && zen_getcookie('admq') == '' && $current_page_base != 'popup_questionaire' && $current_page_base != 'popup_image' && $current_page_base != 'popup_image_additional' && TEST_ENVIRONMENT !== 'true') {
        /*if(DOMAIN != 'hjemfint.dk'){
            require($google_analytics_template_dir . '/google_analytics.php');
            $SimpleCache_replace_array['##ANALYTICS_HTML##'] = $analytics_html;
        }else{
            //$SimpleCache_replace_array['##ANALYTICS_HTML##'] = '<span id="no"></span>';
        }*/

        $SimpleCache_replace_array['##GTAG##'] = '<script>dl.trck = true;</script>';
        if(defined('TAG_MANAGER_ID') && TAG_MANAGER_ID != ''){
            require($google_analytics_template_dir . '/gtm.php');
            $SimpleCache_replace_array['##GTAG##'] .= $gtag_html;
        }else{
            require($google_analytics_template_dir . '/gtag.php');
            $SimpleCache_replace_array['##GTAG##'] .= $gtag_html;            
        }
    }else{
        //$SimpleCache_replace_array['##GTAG##'] = '<script>dl.trck = false;</script>';


        // MIG TEST
        

        if(defined('TAG_MANAGER_ID') && TAG_MANAGER_ID != ''){
            $SimpleCache_replace_array['##GTAG##'] = '<script>dl.trck = true;</script>';
            require($google_analytics_template_dir . '/gtm.php');
            $SimpleCache_replace_array['##GTAG##'] .= $gtag_html;
        }else{
            //require($google_analytics_template_dir . '/gtag.php');
            //$SimpleCache_replace_array['##GTAG##'] .= $gtag_html;            
        }
        // end mig test



    }
}



/*
$SimpleCache_replace_array['##GTM_HEADER##'] = '<!--GTM_HEADER-->';
$SimpleCache_replace_array['##GTM_BODY##'] = '<!--GTM_BODY-->';
if ($_SERVER['REMOTE_ADDR'] == '153.92.126.89') {
    require($google_analytics_template_dir . '/gtm.php');
    $SimpleCache_replace_array['##GTM_HEADER##'] = $gtm_html_header;
    $SimpleCache_replace_array['##GTM_BODY##'] = $gtm_html_body;
}
*/
$SimpleCache_replace_array['##GTM_BODY##'] = '<!--GTM_BODY-->';
if(defined('TAG_MANAGER_ID')){
    if(TAG_MANAGER_ID != ''){
        $gtm_html_body = '<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . TAG_MANAGER_ID . '"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->';
        $SimpleCache_replace_array['##GTM_BODY##'] = $gtm_html_body;
    }
}
$uhistory = '{}';
if (is_array($_SESSION['history'])) {
    $uhistory = $_SESSION['history'];
    unset($uhistory['keyword']);
    $uhistory = json_encode($uhistory);
}
$SimpleCache_replace_array['##ANALYTICS_HTML##'] .= '<script>uhistory = ' . $uhistory . ';</script>';


/*$SimpleCache_replace_array['##DYNAMIC_SRC##'] = '<!--DYNAMIC_SRC-->';
if (isset($_SESSION['view_port_width'])) {
    if ($_SESSION['view_port_width'] > 768) {
        $SimpleCache_replace_array['##DYNAMIC_SRC##'] = 'src';
    } else {
        $SimpleCache_replace_array['##DYNAMIC_SRC##'] = 'data-src';
    }
}*/

$SimpleCache_replace_array['##TIME_COUNTDOWN##'] = '<!--TIME_COUNTDOWN-->';
if (!in_array($current_page_base, explode(",", 'fec_confirmation,checkout_payment_address,checkout_shipping_address')) && CUTOFF_TIME_FOR_SHIPPING_TODAY != '' && CUTOFF_TIME_FOR_SHIPPING_TODAY > 0) {
    $next_delivery_text = get_next_delivery_time();
    if ($next_delivery_text != '') {
        $SimpleCache_replace_array['##TIME_COUNTDOWN##'] = '<div class="time_countdown">' . $next_delivery_text . '</div>';
    }
}
$SimpleCache_replace_array['##TIME_COUNTDOWN##'] = '';

if(strpos(DB_DATABASE, 'nav_') !== false){
    $exclude_cache = true;
}

if (($exclude_cache || (admin_exclude_cache_category_view() == 'no_cache')) || !SimpleCache::startBlock($cache_page_type, true, false, SIMPLE_CACHE_DEFAULT_TIME, $SimpleCache_replace_array)):
    require($template->get_template_dir('html_header.php', DIR_WS_TEMPLATE, $current_page_base,'common') . '/html_header.php');
    timer_log_configure('after html_header');


    /**
     * Define Template Variables picked up from includes/main_template_vars.php unless a file exists in the
     * includes/pages/{page_name}/directory to overide. Allowing different pages to have different overall
     * templates.
     */
    require($template->get_template_dir('main_template_vars.php',DIR_WS_TEMPLATE, $current_page_base,'common'). '/main_template_vars.php');
    timer_log_configure('after main_template_vars');
    /**
     * Read the "on_load" scripts for the individual page, and from the site-wide template settings
     * NOTE: on_load_*.js files must contain just the raw code to be inserted in the <body> tag in the on_load="" parameter.
     * Looking in "/includes/modules/pages" for files named "on_load_*.js"
     */
    $directory_array = $template->get_template_part(DIR_WS_MODULES . 'pages/' . $current_page_base, '/^on_load_/', '.js');
    foreach ($directory_array as $value) {
        $onload_file = DIR_WS_MODULES . 'pages/' . $current_page_base . '/' . $value;
        $read_contents = '';
        $lines = @file($onload_file);
        foreach ($lines as $line) {
            $read_contents .= $line;
        }
        $za_onload_array[] = $read_contents;
    }
    /**
     * now read "includes/templates/TEMPLATE/jscript/on_load/on_load_*.js", which would be site-wide settings
     */
    $directory_array = array();
    $tpl_dir=$template->get_template_dir('.js', DIR_WS_TEMPLATE, 'jscript/on_load', 'jscript/on_load_');
    $directory_array = $template->get_template_part($tpl_dir, '/^on_load_/', '.js');
    foreach ($directory_array as $value) {
        $onload_file = $tpl_dir . '/' . $value;
        $read_contents = '';
        $lines = @file($onload_file);
        foreach ($lines as $line) {
            $read_contents .= $line;
        }
        $za_onload_array[] = $read_contents;
    }
    timer_log_configure('after jscript/on_load');
    /*if (is_array($za_onload_array)) {
        e_log($za_onload_array);
    }*/
    // set $zc_first_field for backwards compatibility with previous version usage of this var
    if (isset($zc_first_field) && $zc_first_field !='')
        $za_onload_array[] = $zc_first_field;

    $zv_onload = "";
    if (isset($za_onload_array) && count($za_onload_array) > 0)
        $zv_onload = implode(';', $za_onload_array);

    //ensure we have just one ';' between each, and at the end
    $zv_onload = str_replace(';;', ';', $zv_onload . ';');

    // ensure that a blank list is truly blank and thus ignored.
    if (trim($zv_onload) == ';')
        $zv_onload='';








    $logo_path = 'images/graphics/dynamic_graphics/logos/www.' . DOMAIN . '.png';
    if (file_exists(DIR_FS_CATALOG . $logo_path)) {
        $logo = HTTPS_SERVER . '/' . $logo_path . '?' . filemtime(DIR_FS_CATALOG . $logo_path);
        $logo_include = '
            "logo": "' . $logo . '",';
    }
    $international_phone = '+46' . substr(str_replace(' ', '-', STORE_PHONE), 1);
    /*$site_avg_query = $db->Execute("select count(reviews_rating) as count, avg(reviews_rating) as average_rating
                        from " . TABLE_REVIEWS . " r
                        left join " . TABLE_REVIEWS_DESCRIPTION . " rd on (r.reviews_id = rd.reviews_id and rd.languages_id = '" . (int)$_SESSION['languages_id'] . "')
                        where r.products_id = '0'
                        and r.reviews_rating > 0
                        limit 1");
    e_log($site_avg_query->fields);*/
    $main_store_name = strtok(STORE_NAME, '.');
    if ($ssheet != 'brand') {
        $main_structured_data['@context'] = 'http://schema.org';
        $main_structured_data['@type'] = 'Organization';
        $main_structured_data['name'] = $main_store_name;
        $main_structured_data['alternateName'] = STORE_NAME;
        $main_structured_data['url'] = HTTPS_SERVER;
        if ($logo != '') {
            $main_structured_data['logo'] = $logo;
        }

        $contactPoint['@type'] = 'ContactPoint';
        $contactPoint['contactType'] = 'customer support';
        $contactPoint['telephone'] = $international_phone;
        $contactPoint['email'] = STORE_OWNER_EMAIL_ADDRESS;
        $contactPoint['areaServed'][] = COUNTRY_CODE_AREA_SERVED;
        $main_structured_data['contactPoint'] = $contactPoint;

        if (FACEBOOK_PAGE != '') {
            $main_structured_data['sameAs'][] = FACEBOOK_PAGE;
        }
        if (INSTAGRAM_PAGE != '') {
            $main_structured_data['sameAs'][] = INSTAGRAM_PAGE;
        }
        add_stuctured_data($main_structured_data);

        $WebSite_structured_data['@context'] = 'http://schema.org';
        $WebSite_structured_data['@type'] = 'WebSite';
        $WebSite_structured_data['url'] = HTTPS_SERVER;
        $potentialAction['@type'] = 'SearchAction';
        $potentialAction['target'] = HTTPS_SERVER . '/index.php?main_page=advanced_search_result&keyword={search_term_string}';
        $potentialAction['query-input'] = 'required name=search_term_string';
        $WebSite_structured_data['potentialAction'] = $potentialAction;
        add_stuctured_data($WebSite_structured_data);
/*?>
        <script type="application/ld+json">
            {
                "@context": "http://schema.org",
                "@type": "Organization",
                "name": "<?php echo $main_store_name; ?>",
                "alternateName": "<?php echo STORE_NAME; ?>",
                "url": "<?php echo HTTPS_SERVER;?>",<?php echo $logo_include; ?>
                "contactPoint": [
                    {
                        "@type": "ContactPoint",
                        "contactType": "customer support",
                        "telephone": "<?php echo $international_phone; ?>",
                        "email": "<?php echo STORE_OWNER_EMAIL_ADDRESS; ?>",
                        "areaServed": [ "SE" ]
                    }
                ],
                "sameAs": [
                    "<?php echo FACEBOOK_PAGE; ?>",
                    "<?php echo INSTAGRAM_PAGE; ?>"
                ],
                "potentialAction": {
                    "@type": "SearchAction",
                    "target": "<?php echo HTTPS_SERVER; ?>/index.php?main_page=advanced_search_result&keyword={search_term_string}",
                    "query-input": "required name=search_term_string"
                }
            }
        </script>

<?php*/
    }













    /**
     * Define the template that will govern the overall page layout, can be done on a page by page basis
     * or using a default template. The default template installed will be a standard 3 column layout. This
     * template also loads the page body code based on the variable $body_code.
     */



    require($template->get_template_dir('tpl_main_page.php', DIR_WS_TEMPLATE, $current_page_base,'common') . '/tpl_main_page.php');
    timer_log_configure('after tpl_main_page');



    echo get_stuctured_data_scripts();

    //END FOOTER CACHE
    if (SIMPLE_CACHE_STATUS == 'true') {
        if (!$exclude_cache)
            SimpleCache::End();
    }
endif
?>
<?php
echo $jscripts_from_html_header_and_page;
if (is_array($jscripts_includes_array_from_html_header)) {
    foreach ($jscripts_includes_array_from_html_header as $jscript_inlcude_file) {
        include($jscript_inlcude_file);
    }
}





if ($_GET['action'] == 'send_sessions_data') {
    $printrr .= '<b>Cookie:</b><br/><pre>' . print_r($_COOKIE, 1). '</pre>';
    $printrr .= '<b>Session:</b><br/><pre>' . print_r($_SESSION, 1). '</pre>';
    $printrr .= '<b>Customer:</b><br/><pre>' . print_r($order, 1). '</pre>';
    zen_mail($subject, 'elias.holmer@netray.se', STORE_NAME . ' send_sessions_data', 'elias.holmer@netray.se', STORE_NAME, EMAIL_FROM, $printrr, '', '');
}



echo $jscripts_from_html_header_and_page_uncached;

if (zen_getcookie('zenAdminID') == '' && zen_getcookie('admq') == '' && $current_page_base != 'popup_questionaire' && $current_page_base != 'popup_image' && $current_page_base != 'popup_image_additional') {
    require($template->get_template_dir('.php',DIR_WS_TEMPLATE, $current_page_base,'jscript') . '/jquery_localstorage.php');
    //echo $jscripts_localstorage;
}


if ($ssheet != 'brand') {
    require($google_analytics_template_dir . '/parameters.php');
    timer_log_configure('end parameters');

    timer_log_configure('end page');
    require($google_analytics_template_dir . '/super_analytics.php');
    timer_log_configure('end super_analytics');

    require($google_analytics_template_dir . '/social_includes.php');
    timer_log_configure('end social_includes');
    require($google_analytics_template_dir . '/async_tasks.php');
    timer_log_configure('end async_tasks');
    echo $gtag_script_bottom_of_page;
    if (zen_getcookie('zenAdminID') == '' && zen_getcookie('admq') == '' && !$isbot && !$isprefetch) {
        
        if(defined('TAG_MANAGER_ID') && TAG_MANAGER_ID == ''){
            //require($google_analytics_template_dir . '/google_dynamic_remarketing.php');
            timer_log_configure('end google_dynamic_remarketing');
            //require($google_analytics_template_dir . '/adroll.php');
            if(ADRECORD_PROGRAM_ID != ''){
                require($google_analytics_template_dir . '/adrecord.php');
                timer_log_configure('end adrecord');
            }
            if(ADTRACTION_TRACKING_ON == '1'){
                require($google_analytics_template_dir . '/adtraction.php');
            }
            if(defined('KLAVIYO_PUBLIC_API_KEY') && KLAVIYO_PUBLIC_API_KEY != ''){
                require($google_analytics_template_dir . '/klaviyo.php');
            }
            require($google_analytics_template_dir . '/facebook.php');
            timer_log_configure('end facebook');
            require($google_analytics_template_dir . '/bing.php');
            require($google_analytics_template_dir . '/pinterest.php');
            timer_log_configure('end bing');
            if (CRITEO_ACCOUNT_ID != '') {
                //require($google_analytics_template_dir . '/criteo.php');
                //require($google_analytics_template_dir . '/hotjar.php');
            }



            if (HTTP_SERVER == 'http://www.hemfint.se') {
                //require($google_analytics_template_dir . '/clickcease.php');
                //require($google_analytics_template_dir . '/improvely.php');
                //require($google_analytics_template_dir . '/perfectaudience.php');
                //require($google_analytics_template_dir . '/hotjar.php');
            }
        }
    }


    require($google_analytics_template_dir . '/log_used_constants.php');
    timer_log_configure('end body');
}

if (zen_getcookie('zenAdminID') != '' && zen_getcookie('admq') != '') {
    require(DIR_WS_MODULES . 'template_ai/admin.php');
    echo $jscripts_admin_bar;
    //print_r($jscripts_admin_bar_array);
    if (is_array($jscripts_admin_bar_array)) {
        foreach ($jscripts_admin_bar_array as $jscripts_admin_bar_array_file) {
            include($jscripts_admin_bar_array_file);
        }
    }
}
timer_log_configure('end scripts');
//BEGIN OLARK
/*if (defined('OLARK_ID') && OLARK_ID != '') { //4983-185-10-6152
?>
    <script type="text/javascript" async> ;(function(o,l,a,r,k,y){if(o.olark)return; r="script";y=l.createElement(r);r=l.getElementsByTagName(r)[0]; y.async=1;y.src="//"+a;r.parentNode.insertBefore(y,r); y=o.olark=function(){k.s.push(arguments);k.t.push(+new Date)}; y.extend=function(i,j){y("extend",i,j)}; y.identify=function(i){y("identify",k.i=i)}; y.configure=function(i,j){y("configure",i,j);k.c[i]=j}; k=y._={s:[],t:[+new Date],c:{},l:a}; })(window,document,"static.olark.com/jsclient/loader.js");
        // custom configuration goes here (www.olark.com/documentation)
        //olark.configure('system.hb_primary_color', '#744da8');

        //olark.configure('system.hb_detached', true);
        //olark.configure('system.give_location_to_operator', true);
        //olark.configure('locale.welcome_title', 'Chata med oss!')
        olark.identify('4983-185-10-6152');
    </script>
    <script>
        jQuery('.dynamic_store_phone_no').mouseover(function() {
            olark('api.chat.onOperatorsAvailable', function() {
                if($(window).width() > 600){
                    olark('api.box.expand');
                }else{
                    olark('api.box.show');
                }
            });
        });
        olark('api.chat.onOperatorsAway', function() {
            olark('api.box.hide');
        });
        olark('api.visitor.getDetails', function(details){
            if(!details.isConversing){
                olark('api.box.shrink');
            }
            //console.log(details.isConversing);
            //if (details.referredByPaidAdvertisingThisVisit) {
            //    olark('api.chat.updateVisitorNickname',{
            //        snippet: "AdWords Referral"
            //    })
            //} else if (details.searchTextForThisVisit == "buying widgets") {
            //    olark('api.chat.updateVisitorNickname', {
            //        snippet: "wants to buy a widget"
            //    })
            //
            //}
        });
    </script>
    <style>
        a.dynamic_store_phone_no{
            color:#333;
        }
        a.dynamic_store_phone_no:hover{
            color:#000;
            text-decoration:none;
        }
    </style>
<?php
    /*
    /*
    if (errorOnPage) {
        olark('api.box.expand')
        olark('api.box.show');
        olark('api.box.hide');
        olark('api.box.shrink');
    }
    olark('api.box.onShow', function() {
        olark('api.chat.updateVisitorNickname', {
            snippet: "is visible"
        });
    });

    document.getElementById('talk-to-sales').onclick = function() {
        olark('api.chat.setOperatorGroup', {
            group: 'abcdef123456'
        });
        olark('api.box.expand');
    }
}*/
//EN OLARK
?>

<script type="text/javascript">
    function replace_broken_images(){
        $('img').each(function(){
            if($(this).attr('cdn') != 'fixed'){

                var subdomain_part = '<?php echo '/' . SUBDOMAIN_URL_PART;?>';
                $(this).attr('cdn','fixed');
                var substr = ['src', 'data-src','data-srcset', 'srcset'];
                var image = $(this);
                $.each(substr , function(index, val) {

                    var broken_img = $(image).attr(val);

                    //console.log(val + ' ' + $(image).attr("'" + val + "'") + ' ' + $(image).attr(val) + ' ' + $(image).attr('src'));
                    if( typeof broken_img === 'undefined' || broken_img === null ){

                    }else{
                        //console.log(val + ' ' + $(image).attr("'" + val + "'") + ' ' + $(image).attr(val) + ' ' + $(image).attr('src'));
                        var new_img = broken_img.replace('/cdn3.', subdomain_part);
                        var new_img = new_img.replace('/cdn2.', subdomain_part);
                        var new_img = new_img.replace('/cdn1.', subdomain_part);
                        var new_img = new_img.replace('/cdn.', subdomain_part);
                        var new_img = new_img.replace('/static.', subdomain_part);
                        $(image).attr(val, new_img);
                    }
                });
            }
        });
    }

    function handleError() {
        //check_cdn();
        replace_broken_images();
    }
    $('img').on("error", handleError);

    function check_cdn() {
        $.ajax({
            url: "https://cdn.<?php echo DOMAIN;?>",
            error: function(){
                replace_broken_images();
            },
            success: function(){
                //do something
            },
            timeout: 1000 // sets timeout to 3 seconds
        });
    }

    //check_cdn();
</script>
<style>
     .read-more-link {
   /* margin-left: 0.5rem; /* small spacing after text */
    text-decoration: underline; /* optional, makes it look like a link */
    color: #007bff; /* bootstrap blue, optional */
    cursor: pointer;
    }
    .modal_link.attribs_info{
        background: transparent url(/includes/templates/template_ai/images/icons/info-icon.png) no-repeat;
        cursor: pointer;
        color: #09F;
        width: 23px;
        height: 23px;
        margin-left: 5px;
        margin-bottom: -10px;
        display: inline-block;
    }




    .modal_link.attribs_info{
        background: transparent url(/includes/templates/template_ai/images/icons/info-icon.png) no-repeat;
        cursor: pointer;
        color: #09F;
        width: 23px;
        height: 23px;
        margin-left: 5px;
        margin-bottom: -10px;
        display: inline-block;
    }


    /*
    .special_price_listing, .rec_savings_listing, .savingsonprice_graph, .cartaddreal .productPrices .savingsonprice_graph {
        background: #000 !important;
        color: #FFF;
    }

    .customer_info_row {
        background: #000;
        color: #fff;
    }
    div#dropMenu li a {
        color: #fff;
    }
    div#dropMenu li a:hover {
        color: #f5f5f5;
    }
    */
</style>

<?php 







?>

<style type="text/css">



    







    /* /END REPLACE */










    @media handheld, screen and (max-width:860px){
        .ccConsent.large, .ccConsent.cookie_settings_view{
              transform: none;
            left: 0;
        }
    }
    @media handheld, screen and (max-width:460px){
        .ccConsent.large{
            bottom: auto;
            top: 60px;
            padding: 10px 2% 20px;
              transform: none;
            left: 0;
        }
    }

    @media handheld, screen and (max-width:360px){
        .cookie_settings_holder{
            font-size: 10px;
        }
        .cookie_settings_header{
            font-size: 12px;
        }
        .cookieTextHolderExtended .cookie_settings_header_text,{
            padding: 0px
        }
        .cookieTextHolderExtended .cookie_settings_header{
            padding-bottom: 0px
        }
        .cookie_settings_text{
            padding: 0px 0 7px;
        }
        .ccConsent.large{
            bottom: auto;
        }
    }
    
</style>
<script type="text/javascript">

jQuery(function() { 
    <?php
    if (TAG_MANAGER_ID == '' ){
    ?>
        function setConsentMode(ConsentMode) {
            //loadAnalytics();
            ConsentModeObj = JSON.parse(ConsentMode);
            if(ConsentModeObj['analytics_storage'] == 'enable'){
                var storage_allowed = {
                  'ad_storage': 'granted',
                  'analytics_storage': 'granted',
                  'ad_user_data': 'granted'
                };
                
            }else{
                var storage_allowed = {
                  'ad_storage': 'denied',
                  'analytics_storage': 'denied',
                  'ad_user_data': 'denied'
                };
            }
            if(ConsentModeObj['allow_rm'] == 'enable'){
                storage_allowed['ad_personalization'] = 'granted';
            }else{
                storage_allowed['ad_personalization'] = 'denied';
            }
            if(window.gtag){
                gtag('consent', 'update', storage_allowed);
            }
            
            if(window.uetq){
                window.uetq.push('consent', 'update', storage_allowed);
            }
            


        }
    <?php
    }
    ?>


    <?php echo $set_cookie_js;?>
    var set_consent = false;
    var consent_storage_string = getCookie('consent_storage');
    if(consent_storage_string != '' && consent_storage_string != 'deleted'){
       var consent_storage_obj = $.parseJSON(consent_storage_string); 
    }else{
       var consent_storage_obj = {}; 
    }
    

    jQuery('.cookieButton').live('click', function() {
        alert();
        var allow_all = false;
        if(jQuery(this).hasClass('all')){
            allow_all = true;
        }

        var ConsentData = {'storage': {}};
        
        set_consent = true;
        consent_storage_obj["integterms"] = 1;
        ConsentData['storage']["integterms"] = 1;

        var allow_rm_enabled = 'yes';
        if(jQuery('input[name=disallow_rm]:checked').length && !allow_all){
            allow_rm_enabled = 'no';
        }else{
            allow_rm_enabled = 'yes';
        }
        if(allow_all){
            allow_rm_enabled = 'yes';
        }

        consent_storage_obj["allow_rm"] = allow_rm_enabled;
        ConsentData['storage']["allow_rm"] = allow_rm_enabled;

        $('.ccConsent input[type=checkbox]').each(function () {
            var c_name = $(this).attr('name');
            if (this.checked) {
                var c_enabled = 'disable';
            }else{
                var c_enabled = 'enable';
            }
            if(allow_all){
                c_enabled = 'enable';
            }
           consent_storage_obj[c_name] = c_enabled;
           ConsentData['storage'][c_name] = c_enabled;
        });


        if(set_consent){
            <?php
            if (TAG_MANAGER_ID == '' ){
            ?>
                setConsentMode(JSON.stringify(consent_storage_obj));
            <?php
            }
            ?>
            
            
            //console.log('set consent_storage');
            //console.log(consent_storage_obj);
            //console.log(JSON.stringify(consent_storage_obj));
        }

        jQuery.ajax({
              type: "POST",
              data: ConsentData,
              url: "/ajax/ajax_cookie_settings.php",
              success: function(returndata) {
                    c_data = JSON.parse(returndata);
                    sa = c_data.sa;
                    page_view.analytics_user_id = c_data.sa;
                    if(consent_storage_obj['analytics_storage'] == 'enable'){
                        collectTracking(tracking_layer);
                        tracking_layer = {};
                        tracking_layer.clicks = [];
                        tracking_layer.impressions = [];
                    }

                    //update consent
            }
        });
        
        jQuery('.ccConsentHolder').remove();
    });

    if(jQuery('.ccConsent').length){
        
        /*if(consent_storage_obj['cookieConsentSZ'] == 'l'){
            jQuery('.ccConsent').removeClass('small').removeClass('medium').addClass('large');
        }else{
            jQuery('.ccConsent').removeClass('large').removeClass('medium').addClass('small');
        }*/
    }

    function open_cookie_setting(){
        if(jQuery('.ccConsent').length){
            jQuery('.ccConsent').addClass('cookie_settings_view');

            jQuery('.ccConsent').removeClass('large').removeClass('medium');
            jQuery('.ccConsent').show();
            jQuery('.ccConsentHolder').show(); 

        }else{

            jQuery.ajax({
                  /*type: "POST",
                  data: frmdata,*/
                  url: "/ajax/ajax_cookie_settings.php",
                  success: function(returndata) {
                        jQuery(returndata).insertAfter('#mainWrapper');
                        jQuery('.ccConsent').addClass('cookie_settings_view');
                        jQuery('.ccConsent').removeClass('large').removeClass('medium');
                }
            });
        }

    };
    jQuery('a[href*="#setcookies"]').live('click', function(event) {
        open_cookie_setting();
        event.preventDefault();
    }); 
    jQuery('.cookieSettings').live('click', function() {
        open_cookie_setting();
    });



}); 


            function check_integterms(milliseconds) {
                if(jQuery('.ccConsentHolder').length){
                    var consent_storage_string = getCookie('consent_storage');
                    if(consent_storage_string != '' && isJson(consent_storage_string)){
                        var consent_storage_obj = $.parseJSON(consent_storage_string);
                        if(consent_storage_obj['integterms'] == 1){
                            jQuery('.ccConsentHolder').remove();
                        }                 
                    }

                    if(jQuery('.ccConsentHolder').length){
                        setTimeout(function() {
                            check_integterms(milliseconds+1000);
                        }, milliseconds)
                    }
                    
                }
            }

            check_integterms(5000);


              
        </script>
    <?php 
    if ($_SERVER['REMOTE_ADDR'] == '153.92.126.89'){
        ?>
<script>

jQuery(function() { 
        var cat_page_added_to_history = false;

        /* start temp migration */
        if (typeof(Storage) !== "undefined") {
            /*localStorage.setItem('cart-se-state', '{"id":14,"items":[{"id":"item-0","productId":"522","qty":1,"configuration":[{"ConfigurableOption":null,"optionId":"color","messages":[],"choice":"45"}]},{"id":"item-1","productId":"181","qty":1,"configuration":[]},{"id":"item-2","productId":"209","qty":1,"configuration":[]}],"email":"","billingAddress":{"newsletter":"SUBSCRIBED","postcode":"1234"},"selectedPaymentMethod":null,"deliveries":[{"id":"1","shippingAddress":{"country":"SE","newsletter":"SUBSCRIBED","postcode":"1234"},"shippingMethod":"tieredtablerates_fidsv","items":{},"state":{"partDeliverySelected":true,"prioritizedFreight":false,"selectedDeliveryDate":null}}],"lastDeliveryId":1,"vatNumber":"","itemInc":3,"couponCode":"","comment":"","newsletter":true}');*/

            const localStorageCart = JSON.parse(localStorage.getItem('cart-se-state'));
            //console.log(localStorageCart);
            if(localStorageCart != null) {
                if(localStorageCart.items !== null) {
                    for (ci in localStorageCart.items) {
                        var quantity = localStorageCart.items[ci].qty;
                        var products_identifer = localStorageCart.items[ci].productId*1 + 10000;
                        var dataString = "product_action=change_quantity&cart_quantity=1&products_id=" + products_identifer;
                        //add_to_cart(products_identifer, dataString, 'no');
                        console.log(dataString);
                        $.ajax({
                          type: "POST",
                          url: "/ajax/ajax_checkout_totals.php",
                          async: true,
                          data: dataString,
                          success: function(returndata) {
                          }
                      });
                    }
                    localStorage.removeItem('cart-se-state');
                }
                    






              }/*
              var ConsentData = {"storage":{"integterms":"1","allow_rm":"enable","functionality_storage":"enable","security_storage":"enable","personalization_storage":"enable","analytics_storage":"enable"}};
                    jQuery.ajax({
                      type: "POST",
                      data: ConsentData,
                      url: "/ajax/ajax_cookie_settings.php",
                      success: function(returndata) {
                            c_data = JSON.parse(returndata);
                            sa = c_data.sa;
                            page_view.analytics_user_id = c_data.sa;
                            if(consent_storage_obj['analytics_storage'] == 'enable'){
                                collectTracking(tracking_layer);
                                tracking_layer = {};
                                tracking_layer.clicks = [];
                                tracking_layer.impressions = [];
                            }

                            //update consent
                        }
                    });*/
           
        }
        /* end temp migration */

    function load_recommendations(){
        
        var popupwidth = $('.informationback').width();
        if($(window).width() < 370){
            var number_of_product_recomendations = 0;
        }else if($(window).width() < popupwidth){
            var number_of_product_recomendations = 2;
        }else{
            var number_of_product_recomendations = 3;
        }

        if(jQuery('.other_products_suggestions').length){
                jQuery('.other_products_suggestions').addClass('loaded');
                var dataString = '';
                dataString += "current_category_id=" + jQuery('.other_products_suggestions').attr("current_category_id") + "&";
                dataString += "products_id=" + jQuery('.other_products_suggestions').attr("products_id") + "&";
                //dataString += "also_purchased=" + jQuery('.other_products_suggestions').attr("also_purchased") + "&";
                dataString += "products_similiar_key=" + jQuery('.other_products_suggestions').attr("products_similiar_key") + "&";

                        jQuery.ajax({
                              type: "POST",
                              url: "ajax/ajax_products_info.php",
                              async: true,
                              data: dataString,
                              success: function(returndata) {
                                simple_lightbox_close();
                                $('#recommendations_pop').remove();
                                $('<div id="recommendations_pop"></div>').appendTo('#postload_display');
                                $('<div class="pop_rec_wrapper"></div>').appendTo('#recommendations_pop');
                                    jQuery(returndata).filter('.other_products_suggestions').each(function(){
                                        jQuery(jQuery(this).html()).appendTo('.pop_rec_wrapper');
                                    });
                                    jQuery(returndata).filter('.xsell-holder').each(function(){
                                        jQuery('.xsell-holder').html(jQuery(this).html());
                                    });
                                    
                                    simple_lightbox("#recommendations_pop", popupwidth +'px');
                                    link_images();
                                    jQuery('.centerBoxContentsAlso_Cont').each(function() {
                                                    if(jQuery(window).width() <= 768){    
                                                        if(jQuery(this).closest('.horizontal_scroll').length){
                                                            
                                                        }else{
                                                            var tot_width = 0; 
                                                            jQuery(this).wrap('<div class="horizontal_scroll"></div>');
                                                            jQuery(this).addClass('width_adjusted');
                                                            jQuery(this).children().each(function() {
                                                                tot_width += jQuery(this).outerWidth()+10;
                                                            });
                                                            jQuery(this).width(tot_width);
                                                        }
                                                    }
                                                });
                                            
                                }
                        });
        }
    }

   
       // }
        
 });

</script>

<?php
}   
?>
<script>
   jQuery(function() { 
        var showChar = 200; // Number of characters to show
    var ellipsestext = "...";
    

    jQuery('.review_store_test-wrapper').each(function() {
        var $wrapper = jQuery(this);
        var $content = $wrapper.find('.review_store_test');
        var $link = $wrapper.find('.read-more-link');

        var originalHtml = $content.html().trim();
        var contentText = $content.text().trim();
        var readMoreText = $wrapper.data('more');
        var readLessText = $wrapper.data('less');

        if (contentText.length > showChar) {
            // store full HTML in data attribute
            $content.data('original', originalHtml);

            var visibleText = contentText.substr(0, showChar);
            var hiddenText = contentText.substr(showChar);

            var html = '<span class="visible-text">' + visibleText + '</span>' +
                    '<span class="ellipsis">' + ellipsestext + '</span>' +
                    '<span class="more-content" style="display:none;">' + hiddenText + '</span>';

            $content.html(html);

            $link.data('more', readMoreText).data('less', readLessText).text(readMoreText);
        } else {
            $link.hide();
        }
    });


    jQuery('.read-more-link').live('click', function(e) {
        e.preventDefault();
        var $link = jQuery(this);
        var $wrapper = $link.closest('.review_store_test-wrapper');
        var $content = $wrapper.find('.review_store_test');
        var state = $link.attr('data-state');
        var readMoreText = $link.data('more');
        var readLessText = $link.data('less');

        if (state === 'collapsed') {
            // restore full HTML
            var originalHtml = $content.data('original');
            $content.html(originalHtml);
            $link.text(readLessText).attr('data-state', 'expanded');
        } else {
            // collapse back to shortened version
            var contentText = $content.text().trim();
            var visibleText = contentText.substr(0, showChar);
            var hiddenText = contentText.substr(showChar);

            var html = '<span class="visible-text">' + visibleText + '</span>' +
                    '<span class="ellipsis">' + ellipsestext + '</span>' +
                    '<span class="more-content" style="display:none;">' + hiddenText + '</span>';

            $content.html(html);
            $link.text(readMoreText).attr('data-state', 'collapsed');
        }
    });
    });
</script>

</body>
</html>

<?php
/**
 * Load general code run before page closes
 */
if(date('n') == 11){
    ?>
<style type="text/css">
/* Black week */

    .special_price_listing, .rec_savings_listing{
        background: #000;
    }
    .savingsonprice_graph{
         background: #000 !important;   
    }
    .customer_info_row{
        background: #000;
        color: #fff !important;
    }
    div#dropMenu li a, .customer_info_row .arg{
        color: #fff !important;
    }
    div#dropMenu li a {
        color: #fff;
    }

    .productListingOuterBox .recprice{
        display: none;
    }
    .cartaddreal .productPrices .savingsonprice_graph {
        background: #000 !important;
    }
    div#dropMenu li a:hover {
        color: #d7d7d7;
    }
</style>
<?php
}
?>
<?php
/**
 * Load general code run before page closes
 */
if(TEST_ENVIRONMENT == 'true'){
    ?>
    <style type="text/css">
        .customer_info_row{
                background: #9c27b0;
        }
    </style>
    <?php
}
if(!$isbot){
   if ($consent_storage['integterms'] != '1') {
            //e_log('Xookoe ' . $_SERVER['REMOTE_ADDR'] . ' ' . $_COOKIE['zenid']);
            //e_log($_COOKIE);

    } 
}else{
    

    if(preg_match('/(ThecaBot|Qwantify|GPTBot|ImagesiftBot)/i', $_SERVER['HTTP_USER_AGENT'])){
        e_log('Sleep 20 seconds (setting in index.php) ' . $_SERVER['HTTP_USER_AGENT']);
        sleep(20);
    }

}

timer_log_configure('end html');
?>

<?php
require(DIR_WS_INCLUDES . 'application_bottom.php');
timer_log_configure('end application_bottom');
timer_log_configure('-------------------------');


?>
