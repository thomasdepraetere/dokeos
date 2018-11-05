<?php
/* For licensing terms, see /license.txt */

// only this script should have this constant defined. This is used to activate the javascript that
// gives the login name automatic focus in header.inc.html.
/** @todo Couldn't this be done using the $HtmlHeadXtra array? */
define('DOKEOS_HOMEPAGE', true);

// the language file
$language_file = array('courses',  'index',  'admin');

/* Flag forcing the 'current course' reset, as we're not inside a course anymore */
// maybe we should change this into an api function? an example: Coursemanager::unset();
$cidReset = true;

/*
  -----------------------------------------------------------
  Included libraries
  -----------------------------------------------------------
 */

/** @todo make all the library files consistent, use filename.lib.php and not filename.lib.inc.php */
require_once 'main/inc/global.inc.php';
include_once api_get_path(LIBRARY_PATH) . 'course.lib.php';
include_once api_get_path(LIBRARY_PATH) . 'debug.lib.inc.php';
include_once api_get_path(LIBRARY_PATH) . 'events.lib.inc.php';
include_once api_get_path(LIBRARY_PATH) . 'system_announcements.lib.php';
include_once api_get_path(LIBRARY_PATH) . 'groupmanager.lib.php';
include_once api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php';
require_once (api_get_path(LIBRARY_PATH) . 'language.lib.php');
require_once api_get_path(LIBRARY_PATH) . 'sublanguagemanager.lib.php';

// for shopping cart & catalog
require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/EcommerceCatalog.php';
require_once api_get_path(SYS_PATH) . 'main/core/controller/shopping_cart/shopping_cart_controller.php';

if (isset($_GET['action']) && $_GET['action'] == 'makeitpro') {
    header('Location: '.api_get_path(WEB_PATH).'PRO/makeitpro.php?from=installdone');
    exit;
}

$theme_custom_index_page = array('orkyn_tablet');
$stylesheet = api_get_setting('stylesheets');
$is_customized = in_array($stylesheet, $theme_custom_index_page);
if (!$is_customized) {
    header('Location: index.php');
    exit;
}

//$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-1.5.1.min.js" ></script>';
//Code changed like this for testing.
$htmlHeadXtra[] = '<link type="text/css" href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/thiagosf-SkitterSlideshow/css/skitter.styles.css" rel="stylesheet" />';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/thiagosf-SkitterSlideshow/js/jquery.skitter.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/general-functions.js" ></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js"></script>';
$htmlHeadXtra[] = '<link type="text/css" href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui/css/ui-lightness/jquery-ui-1.8.1.custom.css" rel="stylesheet" />';
$htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/chosen/chosen.css"/>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/chosen/chosen.jquery.min.js" ></script>';

$slides_management_table = Database :: get_main_table(TABLE_MAIN_SLIDES_MANAGEMENT);
$rs = Database::query("SELECT * FROM $slides_management_table LIMIT 1");
$row = Database::fetch_array($rs);
$show_slide = $row['show_slide'];
$slide_speed = ($row['slide_speed'] == 0) ? 1 : $row['slide_speed'];
$slide_speed_millisec = intval($slide_speed)*1000;

$htmlHeadXtra[] = '<script type="text/javascript">
$(document).ready( function() {
  $(".chzn-select").chosen({no_results_text: "' . get_lang('NoResults') . '"});  
  if ($(".box_skitter_large").length > 0) {
   $(".box_skitter_large").skitter({
        animation: "random", 
        numbers_align: "center", 
        dots: true, 
        preview: true, 
        focus: false, 
        focus_position: "leftTop", 
        controls: false, 
        controls_position: "leftTop", 
        progressbar: false, 
        animateNumberOver: {"backgroundColor":"#555"}, 
        enable_navigation_keys: true,
        interval: '.$slide_speed_millisec.'
    });
  }
});
</script>
<style  type="text/css">
.smallwhite {
    margin: 0px 15px !important;
}

</style>
';

$loginFailed = isset($_GET['loginFailed']) ? true : isset($loginFailed);
$setting_show_also_closed_courses = api_get_setting('show_closed_courses') == 'true';

// the section (for the tabs)
$this_section = SECTION_CAMPUS;


// Check if we have a CSS with tablet support
$css_info = array();
$css_info = api_get_css_info();
$css_type = !is_null($css_info['type']) ? $css_info['type'] : '';

/*
  -----------------------------------------------------------
  Action Handling
  -----------------------------------------------------------
 */

/** @todo 	Wouldn't it make more sense if this would be done in local.inc.php so that local.inc.php become the only place where authentication is done?
 * 			by doing this you could logout from any page instead of only from index.php. From the moment there is a logout=true in the url you will be logged out
 * 			this can be usefull when you are on an open course and you need to log in to edit something and you immediately want to check how anonymous users
 * 			will see it.
 */
$my_user_id = api_get_user_id();

// logout anonymous user
logout_anonymous();

if (!empty($_GET['logout'])) {
    logout();
}

/*
  -----------------------------------------------------------
  Table definitions
  -----------------------------------------------------------
 */
$main_course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
$main_category_table = Database :: get_main_table(TABLE_MAIN_CATEGORY);
$track_login_table = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_LOGIN);

/*
  -----------------------------------------------------------
  Constants and CONFIGURATION parameters
  -----------------------------------------------------------
 */
/** @todo these configuration settings should move to the dokeos config settings */
/** defines wether or not anonymous visitors can see a list of the courses on the Dokeos homepage that are open to the world */
$_setting['display_courses_to_anonymous_users'] = 'true';

/** @todo remove this piece of code because this is not used */
if (isset($_user['user_id'])) {
    $nameTools = api_get_setting('siteName');
}

/*
  ==============================================================================
  LOGIN
  ==============================================================================
 */

/**
 * @todo This piece of code should probably move to local.inc.php where the actual login / logout procedure is handled.
 * @todo consider removing this piece of code because does nothing.
 */
if (isset($_GET['submitAuth']) && $_GET['submitAuth'] == 1) {
    // nice lie!!!
    echo 'Attempted breakin - sysadmins notified.';
    session_destroy();
    die();
}

//Delete session neccesary for legal terms
if (api_get_setting('allow_terms_conditions') == 'true') {
    unset($_SESSION['update_term_and_condition']);
    unset($_SESSION['info_current_user']);
}

/**
 * @todo This piece of code should probably move to local.inc.php where the actual login procedure is handled.
 * @todo check if this code is used. I think this code is never executed because after clicking the submit button
 * 		 the code does the stuff in local.inc.php and then redirects to index.php or user_portal.php depending
 * 		 on api_get_setting('page_after_login')
 */
if (!empty($_POST['submitAuth'])) {
    // the user is already authenticated, we now find the last login of the user.
    if (isset($_user['user_id'])) {
        $sql_last_login = "SELECT UNIX_TIMESTAMP(login_date)
								FROM $track_login_table
								WHERE login_user_id = '" . $_user['user_id'] . "'
								ORDER BY login_date DESC LIMIT 1";
        $result_last_login = Database::query($sql_last_login, __FILE__, __LINE__);
        if (!$result_last_login) {
            if (Database::num_rows($result_last_login) > 0) {
                $user_last_login_datetime = Database::fetch_array($result_last_login);
                $user_last_login_datetime = $user_last_login_datetime[0];
                api_session_register('user_last_login_datetime');
            }
        }
        mysql_free_result($result_last_login);

        //event_login();
        if (api_is_platform_admin()) {
            // decode all open event informations and fill the track_c_* tables
            include api_get_path(LIBRARY_PATH) . 'stats.lib.inc.php';
            decodeOpenInfos();
        }
    }
} // end login -- if ($_POST['submitAuth'])
else {
    // only if login form was not sent because if the form is sent the user was already on the page.

    event_open();
}

if (isset($_REQUEST['language'])) {
    $language = $_REQUEST['language'];
} else {
    $language = api_get_interface_language();
    // Check if current language has sublanguage
    $language_id = api_get_language_id($language, false);
    $sublanguages_info = SubLanguageManager::get_sublanguage_info_by_parent_id($language_id);
    $platform_language = api_get_setting('platformLanguage');
    if (!empty($sublanguages_info['dokeos_folder']) && $platform_language == $sublanguages_info['dokeos_folder']) {
        $language = $sublanguages_info['dokeos_folder'];
    }
}

// the header
Display :: display_header('', 'dokeos');
//echo '<div id="content" class="maxcontent">';
echo '<div id="content0" class="maxcontent">';

// Plugins for loginpage_main AND campushomepage_main
if (!api_get_user_id()) {
    api_plugin('loginpage_main');
} else {
    api_plugin('campushomepage_main');
}

$home = 'home/';
if ($_configuration['multiple_access_urls']) {
    $access_url_id = api_get_current_access_url_id();
    if ($access_url_id != -1) {
        $url_info = api_get_access_url($access_url_id);
        // "http://" and the final "/" replaced
        $url = substr($url_info['url'], 7, strlen($url_info['url']) - 8);
        $clean_url = replace_dangerous_char($url);
        $clean_url = str_replace('/', '-', $clean_url);
        $clean_url = $clean_url . '/';
        $home_old = 'home/';
        $home = 'home/' . $clean_url;
    }
}

// Including the page for the news
$page_included = false;

$check1 = false;
$show_menu = false;
if ((api_is_allowed_to_create_course() || api_is_session_admin()) && ($_SESSION["studentview"] != "studentenview")) {
$show_menu = true;
}
if(!$show_menu){
     if (!($_user['user_id']) || api_is_anonymous($_user['user_id'])   ) {
     }else{
         $display_open_course = display_open_course(true);
         $show_opened_courses =  api_get_setting('show_opened_courses');
         $display_open_course = isset($display_open_course) ? $display_open_course : false;
         $show_opened_courses = isset($show_opened_courses) ? $show_opened_courses : false;
    
         $check1 = ($display_open_course AND $show_opened_courses == 'true') ? (false) : (true);  
     }
 }
$width = ($check1) ? "style='margin: auto!important;float:none;'" : "";
/*
echo '<div id="content_with_menu" '.$width.'>';
if (api_is_platform_admin()) {
    echo '<div id="edit_homepage_bloc">
			<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/configure_homepage.php">' . Display::return_icon('pixel.gif', get_lang('EditPublicPages'), array('class' => 'actionplaceholdericon actionedit', 'align' => 'middle')) . get_lang('EditPublicPages') . '</a>
		  </div>';
}

$slides_table = Database :: get_main_table(TABLE_MAIN_SLIDES);
if (isset($_REQUEST['language'])) {
    $language = $_REQUEST['language'];
} else {
    $language = api_get_interface_language();
    // Check if current language has sublanguage
    $language_id = api_get_language_id($language, false);
    $sublanguages_info = SubLanguageManager::get_sublanguage_info_by_parent_id($language_id);
    $platform_language = api_get_setting('platformLanguage');
    if (!empty($sublanguages_info['dokeos_folder']) && $platform_language == $sublanguages_info['dokeos_folder']) {
        $language = $sublanguages_info['dokeos_folder'];
    }
}

$sql = "SELECT * FROM $slides_table ORDER BY display_order";
$res = Database::query($sql, __FILE__, __LINE__);
$num_of_slides_total = Database::num_rows($res);

$sql = "SELECT * FROM $slides_table WHERE language = '" . $language . "' ORDER BY display_order";
$res = Database::query($sql, __FILE__, __LINE__);
$num_of_slides = Database::num_rows($res);
$slides = array();
$img_dir = api_get_path(WEB_PATH) . 'home/default_platform_document/';

if ($num_of_slides <> 0) {
    while ($row = Database::fetch_array($res)) {
        $image = $img_dir . $row['image'];
        if (empty($row['title'])) {
            $title = get_lang('Title');
        } else {
            $title = $row['title'];
        }
        if (empty($row['link'])) {
            $link = '#';
        } else {
            $link = $row['link'];
        }
        if (empty($row['alternate_text'])) {
            $alternate_text = get_lang('AltText');
        } else {
            $alternate_text = $row['alternate_text'];
        }
        $slides[] = array('image' => $image, 'title' => $title, 'link' => $link, 'caption' => $row['caption'], 'alttext' => $alternate_text);
    }
} else {
    // If don't exist slides in the site because is the first time
    if ($num_of_slides_total == 0) {

        // Get the array with the languages
        $lang_array = api_get_languages();

        // Loop for array with the languages
        foreach ($lang_array['name'] as $key => $value) {
            $sql = "SELECT * FROM $slides_table WHERE language = '" . Database::escape_string($lang_array["folder"][$key]) . "' ORDER BY display_order";
            $res = Database::query($sql, __FILE__, __LINE__);
            $num_rows = Database::num_rows($res);

            if ($num_rows == 0) {

                //Adding 3 default images into database

                for ($i = 1; $i <= 3; $i++) {

                    // Set the text by default
                    $YourTitle = get_lang('YourTitle' . $i, 'DLTT', $lang_array["folder"][$key]);
                    $YourTitle = ($YourTitle == 'YourTitle' . $i) ? get_lang('YourTitle' . $i) : $YourTitle;
                    $AltText = get_lang('AltText' . $i, 'DLTT', $lang_array["folder"][$key]);
                    $AltText = ($AltText == 'AltText' . $i) ? get_lang('AltText' . $i) : $AltText;
                    $YourCaption = get_lang('YourCaption' . $i, 'DLTT', $lang_array["folder"][$key]);
                    $YourCaption = ($YourCaption == 'YourCaption' . $i) ? get_lang('YourCaption' . $i) : $YourCaption;

                    $sql = "INSERT INTO $slides_table(title,alternate_text,link,caption,image,language,display_order)
                                    VALUES('" . Database::escape_string($YourTitle) . "',
                                            '" . Database::escape_string($AltText) . "',
                                            '#',
                                            '" . Database::escape_string($YourCaption) . "',
                                            '" . substr($lang_array["folder"][$key], 0, 3) . "_slide0$i.jpg',
                                            '" . Database::escape_string($lang_array["folder"][$key]) . "','$i')";
                    Database::query($sql, __FILE__, __LINE__);

                    $updir = api_get_path(SYS_PATH) . 'home/default_platform_document/';
                    $thumbdir = api_get_path(SYS_PATH) . 'home/default_platform_document/template_thumb/';

                    // Makes a copy of the file source to dest
                    @copy($thumbdir . 'thumb_slide0' . $i . '.jpg', $thumbdir . 'thumb_' . substr($lang_array["folder"][$key], 0, 3) . '_slide0' . $i . '.jpg');
                    @copy($updir . 'slide0' . $i . '.jpg', $updir . substr($lang_array["folder"][$key], 0, 3) . '_slide0' . $i . '.jpg');
                }
            }
        }
        $pref_lang = substr(api_get_interface_language(), 0, 3);
        $slides[] = array('image' => $img_dir . $pref_lang . '_slide01.jpg', 'title' => get_lang('YourTitle1'), 'link' => '#', 'caption' => get_lang('YourCaption1'), 'alttext' => get_lang('AltText1'));
        $slides[] = array('image' => $img_dir . $pref_lang . '_slide02.jpg', 'title' => get_lang('YourTitle2'), 'link' => '#', 'caption' => get_lang('YourCaption2'), 'alttext' => get_lang('AltText2'));
        $slides[] = array('image' => $img_dir . $pref_lang . '_slide03.jpg', 'title' => get_lang('YourTitle3'), 'link' => '#', 'caption' => get_lang('YourCaption3'), 'alttext' => get_lang('AltText3'));
        $show_slide = 1;
        $sql = "SELECT * FROM $slides_table WHERE language = '" . Database::escape_string($language) . "' ORDER BY display_order";
        $res = Database::query($sql, __FILE__, __LINE__);
        $num_of_slides = Database::num_rows($res);
    }
}
if (empty($_GET['include']) && $show_slide == 1 && $num_of_slides > 0) {
    echo '<div id="container"><div class="box_skitter box_skitter_large"><ul>';
    foreach ($slides as $slide) {
        echo '<li>';
        $slide_content = '{img}';
        if (!empty($slide['link']) && $slide['link'] != '#') {
            $slide_content = '<a href="' . $slide['link'] . '" title="' . $slide['title'] . '">{img}</a>';
        }
        echo str_replace('{img}', '<img src="' . $slide['image'] . '" width="720" height="240" title="' . $slide['title'] . '" alt="' . $slide['alttext'] . '" />', $slide_content);
        echo '<div class="label_text">';
        if (!empty($slide['caption'])) {
            echo '<p>' . $slide['caption'] . '</p>';
        }
        echo '</div>';
        echo '</li>';
    }
    echo '</ul></div></div>';
//slider ends here
}

// Display courses and category list
if ( ShoppingCartController::create()->isShoppingCartEnabled() )
{
    echo '<script type="text/javascript" src="'.api_get_path(WEB_PATH).'main/appcore/library/jquery/jPaginate/jquery.paginate.js"></script>';
    echo '<link type="text/css" rel="stylesheet" href="'.api_get_path(WEB_PATH).'main/appcore/library/jquery/jPaginate/css/style.css"/>';
echo '<script type="text/javascript">
    
    function listCategory(id_item, id_category, cantidad)
    {
    
        $(".ecommerce_text_category").removeClass("active");        
        $("#cat-"+parseInt(id_category)).addClass("active");

        var cant;
        $("#divPaginator").empty();
        $("#divPaginator").removeAttr("style");
        $("#divPaginator").removeAttr("class");
        $("#ElistCatalog").empty();
        $("#divPaginator").paginate({
                        count 		: cantidad,
                        start 		: 1,
                        display                 : 10,
                        border			: false,
                        text_color  		: "#888",
                        background_color    	: "#EEE",	
                        text_hover_color  	: "black",
                        background_hover_color	: "#CFCFCF",
                        onChange                : function(page){
                                                            $.ajax({
                                                                type: "POST",
                                                                url: "main/index.php?module=ecommerce&cmd=Sessionlist&func=getCoursePaginator&page="+page+"&count=10&id_category="+id_category,
                                                                //data: datos,
                                                                dataType: "json",
                                                                success: function(data){
                                                                $("#ElistCatalog").empty();
                                                                $.each(data, function(i, item) {
                                    var html = "<table width=\'525px\' style=\'margin-bottom:20px;\' class=\'back_table_catalog\'><tr>";
                                        if(item.image != "../../../main/img/pixel.gif")
                                        html+= "<td class=\'box_catalog\'><img  src=\'home/default_platform_document/ecommerce_thumb/"+item.image+" \' /></td>";
                                        html+= "<td class=\'box_catalog2\'><table width=\'100%\'><tr style=\'height:70px;\'><td style=\'vertical-align:top; padding-left:10px;width:400px;\'><strong style=\'font-zise:12px;\'>"+item.title+"</strong><br />"+item.summary+"</td></tr><tr style=\'height:30px\'><td style=\'vertical-align:top; padding-left:10px;width:400px;color:#000;\'><strong>"+item.langprice+": </strong>"+item.priceinfo+" &nbsp;&nbsp;&nbsp;<strong>Dur&eacute;e: </strong>"+item.durationinfo+"</td></tr><tr  style=\'height:20px;\'></tr></table></td></tr></table>";
                                    $("#ElistCatalog").append(html);
                                                                });
                                                                },
                                                                timeout:80000
                                                            });
                                                }
                });

               procesa="main/index.php?module=ecommerce&cmd=Sessionlist&func=getCoursePaginator&page=1&count=10&id_category="+id_category;
                $.getJSON(procesa,
                function(json){
                        $.each(json, function(i, item) {
                                    var html = "<table width=\'525px\' style=\'margin-bottom:20px;\' class=\'back_table_catalog\'><tr>";
                                        if(item.image != "../../../main/img/pixel.gif")
                                        html+= "<td class=\'box_catalog\'><img  src=\'home/default_platform_document/ecommerce_thumb/"+item.image+" \' /></td>";
                                        html+= "<td class=\'box_catalog2\'><table width=\'100%\'><tr style=\'height:70px;\'><td style=\'vertical-align:top; padding-left:10px;width:400px;\'><strong style=\'font-zise:12px;\'>"+item.title+"</strong><br />"+item.summary+"</td></tr><tr style=\'height:30px\'><td style=\'vertical-align:top; padding-left:10px;width:400px;color:#000;\'><strong>"+item.langprice+": </strong>"+item.priceinfo+" &nbsp;&nbsp;&nbsp;<strong>Dur&eacute;e: </strong>"+item.durationinfo+"</td></tr><tr  style=\'height:20px;\'><td style=\'text-align:right;\'><div style=\'float:right;\'><table rel=\'"+item.type+"\' id=\'shoppingCartCatalog\'><tr><td><a href=\'#\' class=\'addToCartCourse\' id=\'"+item.id+"\' onClick=\'return viewDetail(this);\'><span>"+item.seemore+"</span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=\'course_catalog_container\' rel=\'"+item.codeitem+"\'><a class=\'addToCartCourse addToCartCourseClick\' href=\'#\'><span>"+item.addtocart+"</span></a></span></td></tr></table></div></td></tr></table></td></tr></table>";
                                    $("#ElistCatalog").append(html);
                        });
                });
                //$("#ElistCatalog").html("testing");
    }

    function listAll()
    {
        $(".ecommerce_text_category").removeClass("active");        
        $("#cat-0").addClass("active");

        $(document).ready(function(){
               $("#divPaginator").empty();
               $("#divPaginator").removeAttr("style");
               $("#divPaginator").removeAttr("class");
               $("#ElistCatalog").empty();
                $("#divPaginator").paginate({
                                count 		: '.  ceil(count(EcommerceCatalog::create()->getCourseEcommerce())/10).',
                                start 		: 1,
                                display                 : 10,
                                border			: false,
                                text_color  		: "#888",
                                background_color    	: "#EEE",	
                                text_hover_color  	: "black",
                                background_hover_color	: "#CFCFCF",
                                onChange                : function(page){
                                                                    $.ajax({
                                                                        type: "POST",
                                                                        url: "main/index.php?module=ecommerce&cmd=Sessionlist&func=getCoursePaginator&page="+page+"&count=10",
                                                                        //data: datos,
                                                                        dataType: "json",
                                                                        success: function(data){
                                                                        $("#ElistCatalog").empty();
                                                                        $.each(data, function(i, item) {
                                    var html = "<table width=\'525px\' style=\'margin-bottom:20px;\' class=\'back_table_catalog\'><tr>";
                                        if(item.image != "../../../main/img/pixel.gif")
                                        html+= "<td class=\'box_catalog\'><img  src=\'home/default_platform_document/ecommerce_thumb/"+item.image+" \' /></td>";
                                        html+= "<td class=\'box_catalog2\'><table width=\'100%\'><tr style=\'height:70px;\'><td style=\'vertical-align:top; padding-left:10px;width:400px;\'><strong style=\'font-zise:12px;\'>"+item.title+"</strong><br />"+item.summary+"</td></tr><tr style=\'height:30px\'><td style=\'vertical-align:top; padding-left:10px;width:400px;color:#000;\'><strong>"+item.langprice+": </strong>"+item.priceinfo+" &nbsp;&nbsp;&nbsp;<strong>Dur&eacute;e: </strong>"+item.durationinfo+"</td></tr><tr  style=\'height:20px;\'><td style=\'text-align:right;\'><div style=\'float:right;\'><table rel=\'"+item.type+"\' id=\'shoppingCartCatalog\'><tr><td><a href=\'#\' class=\'addToCartCourse\' id=\'"+item.id+"\' onClick=\'return viewDetail(this);\'><span>"+item.seemore+"</span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=\'course_catalog_container\' rel=\'"+item.codeitem+"\'><a class=\'addToCartCourse addToCartCourseClick\' href=\'#\'><span>"+item.addtocart+"</span></a></span></td></tr></table></div></td></tr></table></td></tr></table>";
                                    $("#ElistCatalog").append(html);
                                                                        });
                                                                        },
                                                                        timeout:80000
                                                                    });
                                                        }
                        });
                        
                            procesa="main/index.php?module=ecommerce&cmd=Sessionlist&func=getCoursePaginator&page=1&count=10";
                            $.getJSON(procesa,
                            function(json){
                                    $.each(json, function(i, item) {
                                    var html = "<table width=\'525px\' style=\'margin-bottom:20px;\' class=\'back_table_catalog\'><tr>";
                                        if(item.image != "../../../main/img/pixel.gif")
                                        html+= "<td class=\'box_catalog\'><img  src=\'home/default_platform_document/ecommerce_thumb/"+item.image+" \' /></td>";
                                        html+= "<td class=\'box_catalog2\'><table width=\'100%\'><tr style=\'height:70px;\'><td style=\'vertical-align:top; padding-left:10px;width:400px;\'><strong style=\'font-zise:12px;\'>"+item.title+"</strong><br />"+item.summary+"</td></tr><tr style=\'height:30px\'><td style=\'vertical-align:top; padding-left:10px;width:400px;color:#000;\'><strong>"+item.langprice+": </strong>"+item.priceinfo+" &nbsp;&nbsp;&nbsp;<strong>Dur&eacute;e: </strong>"+item.durationinfo+"</td></tr><tr  style=\'height:20px;\'><td style=\'text-align:right;\'><div style=\'float:right;\'><table rel=\'"+item.type+"\' id=\'shoppingCartCatalog\'><tr><td><a href=\'#\' class=\'addToCartCourse\' id=\'"+item.id+"\' onClick=\'return viewDetail(this);\'><span>"+item.seemore+"</span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=\'course_catalog_container\' rel=\'"+item.codeitem+"\'><a class=\'addToCartCourse addToCartCourseClick\' href=\'#\'><span>"+item.addtocart+"</span></a></span></td></tr></table></div></td></tr></table></td></tr></table>";
                                    $("#ElistCatalog").append(html);
                                    });
                            });                        
                            //$("#ElistCatalog").html("testing");
                    });
    }

    function viewDetail(element)
    {
        $(document).ready(function(){
        $("#divDialog").empty();
            $("#divDialog").dialog("open");
            procesa="main/index.php?module=ecommerce&cmd=Sessionlist&func=getItemDetail&id="+element.id;
            $.getJSON(procesa,
            function(json){
                $("#divDialog").html("<p>"+json.description+"</p>");
                $("#divDialog" ).dialog({
                        autoOpen: true,
                        title: json.title,
                        modal: true,
                        show: "blind",
                        width: 600,
                        height: 450,
                        hide: "explode"
                    });                            
                }); 
            return false;
            });



        return false;
    }
$(function() {
        $("#divPaginator").paginate({
                count 		: '.  ceil(count(EcommerceCatalog::create()->getCourseEcommerce())/10).',
                start 		: 1,
                display     : 10,
                border			: false,
                text_color  		: "#888",
                background_color    	: "#EEE",	
                text_hover_color  	: "black",
                background_hover_color	: "#CFCFCF",
                onChange                : function(page){
                                                    $.ajax({
                                                        type: "POST",
                                                        url: "main/index.php?module=ecommerce&cmd=Sessionlist&func=getCoursePaginator&page="+page+"&count=10",
                                                        //data: datos,
                                                        dataType: "json",
                                                        success: function(data){
                                                        $("#ElistCatalog").empty();
                                                        $.each(data, function(i, item) {
                                    var html = "<table width=\'525px\' style=\'margin-bottom:20px;\' class=\'back_table_catalog\'><tr>";
                                        if(item.image != "../../../main/img/pixel.gif")
                                        html+= "<td class=\'box_catalog\'><img  src=\'home/default_platform_document/ecommerce_thumb/"+item.image+" \' /></td>";
                                        html+= "<td class=\'box_catalog2\'><table width=\'100%\'><tr style=\'height:70px;\'><td style=\'vertical-align:top; padding-left:10px;width:400px;\'><strong style=\'font-zise:12px;\'>"+item.title+"</strong><br />"+item.summary+"</td></tr><tr style=\'height:30px\'><td style=\'vertical-align:top; padding-left:10px;width:400px;color:#000;\'><strong>"+item.langprice+": </strong>"+item.priceinfo+" &nbsp;&nbsp;&nbsp;<strong>Dur&eacute;e: </strong>"+item.durationinfo+"</td></tr><tr  style=\'height:20px;\'><td style=\'text-align:right;\'><div style=\'float:right;\'><table rel=\'"+item.type+"\' id=\'shoppingCartCatalog\'><tr><td><a href=\'#\' class=\'addToCartCourse\' id=\'"+item.id+"\' onClick=\'return viewDetail(this);\'><span>"+item.seemore+"</span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=\'course_catalog_container\' rel=\'"+item.codeitem+"\'><a class=\'addToCartCourse addToCartCourseClick\' href=\'#\'><span>"+item.addtocart+"</span></a></span></td></tr></table></div></td></tr></table></td></tr></table>";
                                    $("#ElistCatalog").append(html);
                                                        });

                                                        },
                                                        timeout:80000
                                                    });
                                        }
        });



});
</script>';
    $lista = EcommerceCatalog::create()->getHtmlCatalog(); //EcommerceCatalog::create()->getProductList(); 
        $display_catalog = api_get_setting('display_catalog_on_homepage'); 
    if ($display_catalog === 'true') {  
    echo $lista;
    }
    echo '<div style="display:none;" id="shoppingMsgBody" title="'.get_lang('ShoppingCart').'">';
    echo '<center><img src="main/img/shopcart_general.png" style="vertical-align:text-bottom;margin-bottom: 20px; margin-top: 35px;"><br/>'.get_lang('AcceptAddItemShoppingCart').'</center>'; 
    echo '</div>';
    echo '<div style="display:none;" id="shoppingMsgBodyAdded">';
    echo '<center><img src="main/img/shopcart_general.png" style="vertical-align:text-bottom;margin-bottom: 20px; margin-top: 35px;"><br/>'.get_lang('ItemAddToShoppingCart').'</center>'; 
    echo '</div>';
}
echo'<div id="divYes" style="display:none;">'.  get_lang('langYes').'</div>';
echo'<div id="divNo"  style="display:none;">'.  get_lang('langNo').'</div>';
if (!empty($_GET['include']) && preg_match('/^[a-zA-Z0-9_-]*\.html$/', $_GET['include'])) {
    $contents = file_get_contents('./' . $home . $_GET['include']);
    $contents = str_replace('{DEFAULT_CSS_PATH}', api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/default.css', $contents);
    $contents = str_replace('{CURRENT_CSS_PATH}', api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/templates.css', $contents);
    $contents = str_replace('{WEB_PATH}', api_get_path(WEB_PATH), $contents);
    // replace to curren template css path
    if (preg_match('!/css/(.+)/templates.css!', $contents, $matches)) {
        $contents = str_replace('/' . $matches[1] . '/templates.css', '/' . api_get_setting('stylesheets') . '/templates.css', $contents);
        $extra_style ="";
    }else{
        $extra_style="style=\"padding-left: 15px;\"";
    }
    // replace to curren default css path
    if (preg_match('!/css/(.+)/default.css!', $contents, $matches)) {
        $contents = str_replace('/' . $matches[1] . '/default.css', '/' . api_get_setting('stylesheets') . '/default.css', $contents);
    }
    echo '<div '.$extra_style.'>';
    echo $contents;
    echo '</div>';
    $page_included = true;
} else {
    $count_lang_availables = LanguageManager::count_available_languages();
    if (!empty($_SESSION['user_language_choice']) && $count_lang_availables > 1) {
        $user_selected_language = $_SESSION['user_language_choice'];
    } else {
        $user_selected_language = api_get_setting('platformLanguage');
    }
    $file_sys_path = api_get_path(SYS_PATH);
    $file_full_path = $file_sys_path . $home . 'home_news_' . $user_selected_language . '.html';
    if (!file_exists($file_full_path)) {

        $home_top_file = $file_sys_path . $home . 'home_top.html';
        if ($_configuration['multiple_access_urls'] == true) {
            $home_top_file = $file_sys_path . $home . 'home_top_' . $user_selected_language . '.html';
        }
        if (file_exists($home_top_file)) {
            $home_top_temp = file($home_top_file);
        } else {
            $home_top_temp = file($home_old . 'home_top.html');
        }
        $home_top_temp = implode('', $home_top_temp);
        $open = str_replace('{rel_path}', api_get_path(REL_PATH), $home_top_temp);
        $open = str_replace('{WEB_PATH}', api_get_path(WEB_PATH), $open);
        $open = str_replace('{CURRENT_CSS_PATH}', api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/templates.css', $open);
        $open = str_replace('{DEFAULT_CSS_PATH}', api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/default.css', $open);
        // replace to curren template css path
        if (preg_match('!/css/(.+)/templates.css!', $open, $matches)) {
            $open = str_replace('/' . $matches[1] . '/templates.css', '/' . api_get_setting('stylesheets') . '/templates.css', $open);
        }
        // replace to curren default css path
        if (preg_match('!/css/(.+)/default.css!', $open, $matches)) {
            $open = str_replace('/' . $matches[1] . '/default.css', '/' . api_get_setting('stylesheets') . '/default.css', $open);
        }
        echo $open;
    } else {
        if (file_exists($home . 'home_top_' . $user_selected_language . '.html')) {
            $home_top_temp = file_get_contents($home . 'home_top_' . $user_selected_language . '.html');
        } else {
            $home_top_temp = file_get_contents($home . 'home_top.html');
        }
        $open = str_replace('{rel_path}', api_get_path(REL_PATH), $home_top_temp);
        $open = str_replace('{WEB_PATH}', api_get_path(WEB_PATH), $open);
        $open = str_replace('{CURRENT_CSS_PATH}', api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/templates.css', $open);
        $open = str_replace('{DEFAULT_CSS_PATH}', api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/default.css', $open);

        // replace to curren template css path
        if (preg_match('!/css/(.+)/templates.css!', $open, $matches)) {
            $open = str_replace('/' . $matches[1] . '/templates.css', '/' . api_get_setting('stylesheets') . '/templates.css', $open);
        }
        // replace to curren default css path
        if (preg_match('!/css/(.+)/default.css!', $open, $matches)) {
            $open = str_replace('/' . $matches[1] . '/default.css', '/' . api_get_setting('stylesheets') . '/default.css', $open);
        }
        echo $open;
    }
}



// Display System announcements
$announcement = isset($_GET['announcement']) ? $_GET['announcement'] : -1;
$announcement = intval($announcement);

if (isset($_user['user_id'])) {
    $visibility = api_is_allowed_to_create_course() ? VISIBLE_TEACHER : VISIBLE_STUDENT;
    $number_of_announcement = SystemAnnouncementManager :: count_announcements($visibility, $announcement);
    if ($number_of_announcement > 0) {        
        echo '<div class="sectiontitle portal_news panel_news">';
        SystemAnnouncementManager :: display_announcements($visibility, $announcement);
        echo '</div>';
    }
} else {
    $number_of_announcement = SystemAnnouncementManager :: count_announcements($visibility, $announcement);
    if ($number_of_announcement > 0) {
        echo '<div class="sectiontitle portal_news panel_news">';
        SystemAnnouncementManager :: display_announcements(VISIBLE_GUEST, $announcement);
        echo '</div>';
    }
}
echo '</div>';*/

$show_menu = false;
if ((api_is_allowed_to_create_course() || api_is_session_admin()) && ($_SESSION["studentview"] != "studentenview")) {
  $show_menu = true;
}

 if($show_menu){
         echo '<div class="menu" id="menu">';
         //display_anonymous_menu();
         display_login();
        if (api_get_setting('show_opened_courses') === 'true') {
            //display_open_course();
        }
        echo '</div>';
 }else{
     if (!($_user['user_id']) || api_is_anonymous($_user['user_id'])   ) {
            echo '<div class="menu" id="menu">';
            //display_anonymous_menu();
            display_login();
            if (api_get_setting('show_opened_courses') === 'true') {
                //display_open_course();
            }
            echo '</div>';
     }  
     
 }

function check_left_empty(){
    if (!($_user['user_id']) || api_is_anonymous($_user['user_id'])) {
        $login = true; 
    }else{
        $login = false;
    }
    $show_opened_courses = api_get_setting('show_opened_courses');
    $display_open_course = display_open_course(true);
    $display_open_course = ($display_open_course == 0) ? true : false;

 
    
    if($display_open_course AND $login AND $show_opened_courses){
        return true;
    }else{
        return false;
    }
    
}
function display_open_course($count=false) {
    $tbl_course = Database :: get_main_table(TABLE_MAIN_COURSE);
    $sql = "SELECT * FROM $tbl_course WHERE visibility = 3";
    if (isset($_SESSION['_user']['user_id']) && $_SESSION['_user']['user_id'] != 0) {
        $sql = "SELECT * FROM $tbl_course WHERE visibility = 3 or visibility=2"; 
    }
    $res = Database::query($sql, __FILE__, __LINE__);
    $num_rows = Database::num_rows($res);
     if($count){
            return $num_rows;
     }
    if ($num_rows <> 0) {
        echo '<div class="menu" id="menu">';
        echo "<div class=\"section\">";
        echo '<div class="row"><div class="form_header">' . get_lang('OpenCourses') . '</div></div><br />';
        echo "	<div class=\"sectioncontent\">";
        while ($row = Database::fetch_array($res)) {
            $title = $row['title'];
            $directory = $row['directory'];

            echo '<a href="' . api_get_path(WEB_COURSE_PATH) . $directory . '/?id_session=0"><img alt="' . $title . '" title="' . $title . '" src="main/img/catalogue_22.png" style="vertical-align:text-bottom;" />&nbsp;' . $title . '</a><br />';
        }
        echo '</div></div></div>';
        echo '</div>';
    }
}

echo '</div>';

// display the footer
Display :: display_footer();

function logout_anonymous() {
    $anonymous_uid = api_get_anonymous_id();
    $logged_uid = api_get_user_id();
    if (api_is_anonymous($logged_uid, true)) {
        api_session_unregister('_user');
    }
}

/**
 * This function handles the logout and is called whenever there is a $_GET['logout']
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 */
function logout() {
    global $_configuration, $extAuthSource;
    // variable initialisation
    $query_string = '';

    if (!empty($_SESSION['user_language_choice'])) {
        $query_string = '?language=' . $_SESSION['user_language_choice'];
    }

    // Database table definition
    $tbl_track_login = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_LOGIN);

    // selecting the last login of the user
    $uid = intval($_GET['uid']);
    $sql_last_connection = "SELECT login_id, login_date FROM $tbl_track_login WHERE login_user_id='$uid' ORDER BY login_date DESC LIMIT 0,1";
    $q_last_connection = Database::query($sql_last_connection, __FILE__, __LINE__);
    if (Database::num_rows($q_last_connection) > 0) {
        $i_id_last_connection = Database::result($q_last_connection, 0, 'login_id');
    }

    if (!isset($_SESSION['login_as'])) {
        $current_date = date('Y-m-d H:i:s', time());
        $s_sql_update_logout_date = "UPDATE $tbl_track_login SET logout_date='" . $current_date . "' WHERE login_id='$i_id_last_connection'";
        Database::query($s_sql_update_logout_date, __FILE__, __LINE__);
    }
    LoginDelete($uid, $_configuration['statistics_database']); //from inc/lib/online.inc.php - removes the "online" status
    //the following code enables the use of an external logout function.
    //example: define a $extAuthSource['ldap']['logout']="file.php" in configuration.php
    // then a function called ldap_logout() inside that file
    // (using *authent_name*_logout as the function name) and the following code
    // will find and execute it
    $uinfo = api_get_user_info($uid);
    if (($uinfo['auth_source'] != PLATFORM_AUTH_SOURCE) && is_array($extAuthSource)) {
        if (is_array($extAuthSource[$uinfo['auth_source']])) {
            $subarray = $extAuthSource[$uinfo['auth_source']];
            if (!empty($subarray['logout']) && file_exists($subarray['logout'])) {
                include_once ($subarray['logout']);
                $logout_function = $uinfo['auth_source'] . '_logout';
                if (function_exists($logout_function)) {
                    $logout_function($uinfo);
                }
            }
        }
    }
    if (api_get_setting('cas_activate') == 'true') {
        require_once(api_get_path(SYS_PATH) . 'main/auth/cas/authcas.php');
        if (cas_is_authenticated() != false) {
            error_log('cas log out');
            cas_logout();
        }
    }
    api_session_destroy();
    header("Location: index.php$query_string");
    exit();
}

/**
 * This function checks if there are courses that are open to the world in the platform course categories (=faculties)
 *
 * @param unknown_type $category
 * @return boolean
 */
function category_has_open_courses($category) {
    global $setting_show_also_closed_courses;

    $user_identified = (api_get_user_id() > 0 && !api_is_anonymous());
    $main_course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
    $sql_query = "SELECT * FROM $main_course_table WHERE category_code='$category'";
    $sql_result = Database::query($sql_query, __FILE__, __LINE__);
    while ($course = Database::fetch_array($sql_result)) {
        if (!$setting_show_also_closed_courses) {
            if ((api_get_user_id() > 0
                    && $course['visibility'] == COURSE_VISIBILITY_OPEN_PLATFORM)
                    || ($course['visibility'] == COURSE_VISIBILITY_OPEN_WORLD)) {
                return true; //at least one open course
            }
        } else {
            if (isset($course['visibility'])) {
                return true; //at least one course (does not matter weither it's open or not because $setting_show_also_closed_courses = true
            }
        }
    }
    return false;
}

function display_create_course_link() {
    echo "<li><a href=\"main/create_course/add_course.php\">" . get_lang("CourseCreate") . "</a></li>";
}

function display_edit_course_list_links() {
    echo "<li><a href=\"main/auth/courses.php\">" . get_lang("SortMyCourses") . "</a></li>";
}

/**
 * Trainers manual, Video tutorials and Trainers training is displayed.
 * 
 * @author Ricardo Garcia Rodriguez <master.ojitos@gmail.com>
 */
function display_trainer_links() {
    $user_selected_language = api_get_interface_language();
    if (!isset($user_selected_language)) {
        $user_selected_language = api_get_setting('platformLanguage');
    }
    $trainers_training_link = 'http://www.dokeos.com/en/training/';
    if ($user_selected_language == "french") {
        $trainers_training_link = 'http://www.dokeos.com/fr/training/';
    }
    echo "<li><a href=\"http://www.dokeos.com/en/trainer-manual/\">" . get_lang('TrainersManual') . "</a></li>";
    echo "<li><a href=\"http://www.dokeos.com/en/video-tutorials/\">" . get_lang('VideoTutorials') . "</a></li>";
    echo "<li><a href=\"" . $trainers_training_link . "\">" . get_lang('TrainersTraining') . "</a></li>";
}


function display_login() {
    global $loginFailed, $_plugins, $_user, $menu_navigation, $css_type;

    $platformLanguage = api_get_setting('platformLanguage');

    if (!($_user['user_id']) || api_is_anonymous($_user['user_id'])) { // only display if the user isn't logged in
        display_login_form();

        if ($loginFailed) {
            handle_login_failed();
        }

        if (api_number_of_plugins('loginpage_menu') > 0) {
            echo '<div class="note" style="background: none">';
            api_plugin('loginpage_menu');
            echo '</div>';
        }
    }
    if (isset($_SESSION['_user']['user_id']) && $_SESSION['_user']['user_id'] != 0) {
        // tabs that are deactivated are added here


        $show_menu = false;
        $show_course_link = false;
                if (api_is_anonymous(api_get_user_id())) {
                    echo "<div class=\"section\">"; 
                } else {
                    echo "<div class=\"section2\">";
                }

        if ((api_is_allowed_to_create_course() || api_is_session_admin()) && ($_SESSION["studentview"] != "studentenview")) {
            $show_menu = true;
        }

        if (api_is_platform_admin() || api_is_course_admin() || api_is_allowed_to_create_course()) {
            $show_course_link = true;
        } else {
            if (api_get_setting('allow_students_to_browse_courses') == 'true') {
                $show_course_link = true;
            }
        }        
        if ($show_menu) {
            echo "<div class=\"section overflow\">";
            if ($css_type == 'tablet') {
                echo api_display_tool_title(get_lang('MenuUser'), 'tablet_title');
                display_create_course_link_tablet();
                display_trainer_links_tablet();
                if ($show_digest_link) {
                    display_digest($toolsList, $digest, $orderKey, $courses);
                }
            } else {
                //echo "<div class=\"section\">";
                echo "	<div class=\"sectiontitle\">" . get_lang("MenuUser") . "</div>";
                echo "	<div class=\"sectioncontent\">";
                echo "		<ul class=\"menulist nobullets\">";
                display_create_course_link();
                display_trainer_links();
                echo "		</ul>";
                echo "	</div>";
                //echo "</div>";
            }
            echo '</div>';
        }

        if (!empty($menu_navigation)) {
            echo "<div class=\"section\">";
            echo "<div class=\"sectiontitle\">" . get_lang("MainNavigation") . "</div>";
            echo '<div class="sectioncontent">';
            echo "<ul class=\"menulist nobullets\">";
            foreach ($menu_navigation as $section => $navigation_info) {
                $current = $section == $GLOBALS['this_section'] ? ' id="current"' : '';
                echo '<li' . $current . '>';
                echo '<a href="' . $navigation_info['url'] . '" target="_self">' . $navigation_info['title'] . '</a>';
                echo '</li>';
                echo "\n";
            }
            echo "</ul>";
            echo '</div>';
            echo '</div>';
        }
    }
    
}

/**
 * Displays the menu for anonymous users:
 * login form, useful links, help section
 * Warning: function defines globals
 * @version 1.0.1
 * @todo does $_plugins need to be global?
 */
function display_anonymous_menu() {
    global $loginFailed, $_plugins, $_user, $menu_navigation, $css_type;

    $platformLanguage = api_get_setting('platformLanguage');

    if (!($_user['user_id']) || api_is_anonymous($_user['user_id'])) { // only display if the user isn't logged in
        display_login_form();

        if ($loginFailed) {
            handle_login_failed();
        }

        if (api_number_of_plugins('loginpage_menu') > 0) {
            echo '<div class="note" style="background: none">';
            api_plugin('loginpage_menu');
            echo '</div>';
        }
    }


    // My Account section
    // Display courses and category list
    if (api_get_setting('display_categories_on_homepage') == 'true') {
        echo '<div class="section">';
        display_anonymous_course_list();
        echo '</div>';
    }


    if (isset($_SESSION['_user']['user_id']) && $_SESSION['_user']['user_id'] != 0) {
        // tabs that are deactivated are added here


        $show_menu = false;
        $show_course_link = false;
                if (api_is_anonymous(api_get_user_id())) {
                    echo "<div class=\"section\">"; 
                } else {
                    echo "<div class=\"section2\">";
                }

        if ((api_is_allowed_to_create_course() || api_is_session_admin()) && ($_SESSION["studentview"] != "studentenview")) {
            $show_menu = true;
        }

        if (api_is_platform_admin() || api_is_course_admin() || api_is_allowed_to_create_course()) {
            $show_course_link = true;
        } else {
            if (api_get_setting('allow_students_to_browse_courses') == 'true') {
                $show_course_link = true;
            }
        }
        
        if ($show_menu) {
            echo "<div class=\"section overflow\">";
            if ($css_type == 'tablet') {
                echo api_display_tool_title(get_lang('MenuUser'), 'tablet_title');
                display_create_course_link_tablet();
                display_trainer_links_tablet();
                if ($show_digest_link) {
                    display_digest($toolsList, $digest, $orderKey, $courses);
                }
            } else {
                //echo "<div class=\"section\">";
                echo "	<div class=\"sectiontitle\">" . get_lang("MenuUser") . "</div>";
                echo "	<div class=\"sectioncontent\">";
                echo "		<ul class=\"menulist nobullets\">";
                display_create_course_link();
                display_trainer_links();
                echo "		</ul>";
                echo "	</div>";
                //echo "</div>";
            }
            echo '</div>';
        }

        if (!empty($menu_navigation)) {
            echo "<div class=\"section\">";
            echo "<div class=\"sectiontitle\">" . get_lang("MainNavigation") . "</div>";
            echo '<div class="sectioncontent">';
            echo "<ul class=\"menulist nobullets\">";
            foreach ($menu_navigation as $section => $navigation_info) {
                $current = $section == $GLOBALS['this_section'] ? ' id="current"' : '';
                echo '<li' . $current . '>';
                echo '<a href="' . $navigation_info['url'] . '" target="_self">' . $navigation_info['title'] . '</a>';
                echo '</li>';
                echo "\n";
            }
            echo "</ul>";
            echo '</div>';
            echo '</div>';
        }
    }

    // help section
    /*     * * hide right menu "general" and other parts on anonymous right menu **** */

    $user_selected_language = api_get_interface_language();
    global $home, $home_old;
    if (!isset($user_selected_language)) {
        $user_selected_language = $platformLanguage;
    }

    $menu_content = '';
    if (!file_exists($home . 'home_menu_' . $user_selected_language . '.html') && file_exists($home . 'home_menu.html') && file_get_contents($home . 'home_menu.html') != '') {
        if (file_exists($home . 'home_menu_' . $user_selected_language . '.html')) {
            $menu_content = file_get_contents($home . 'home_menu_' . $user_selected_language . '.html');
        } else {
            $menu_content = file_get_contents($home . 'home_menu.html');
        }
    } // More section
    elseif (file_exists($home . 'home_menu_' . $user_selected_language . '.html') && file_get_contents($home . 'home_menu_' . $user_selected_language . '.html') != '') {
        $menu_content = file_get_contents($home . 'home_menu_' . $user_selected_language . '.html');
    }

    if (!empty($menu_content)) {
        $menu_content = str_replace('{WEB_PATH}', api_get_path(WEB_PATH), $menu_content);
        if ($css_type == 'tablet') {
            echo '<div class="menu-general nobullets">';
            echo api_display_tool_title(get_lang('MenuGeneral'), 'tablet_title');
            $menu_content = str_replace(array('<li>', '</li>'), array('<div>', '</div>'), $menu_content);
            //echo '<ul>';
            echo $menu_content;
            //echo '</ul>';
            echo '</div>';
        } else {
            echo "<div class=\"section menu-more\">", "<div class=\"sectiontitle\">" . get_lang("MenuGeneral") . "</div>";
            echo '<div class="sectioncontent nobullets">';
            echo $menu_content;
            echo '</div>';
            echo '</div>';
        }
    }

    // link mobile
    if (api_is_mobile() && false) { // Mobile version will be avilable in 3.0
        echo '<div class="menu-general nobullets">';
        echo api_display_tool_title(get_lang('Mobile'), 'tablet_title');
        echo '<a  href="' . api_get_path(WEB_VIEW_PATH) . 'mobile/index.php?init=mobile" >' . get_lang('AccessToMobileVersion') . '</a>';
        echo '</div>';
        ;
    }

    if ($_user['user_id'] && api_number_of_plugins('campushomepage_menu') > 0) {
        echo '<div class="note" style="background: none">';
        api_plugin('campushomepage_menu');
        echo '</div>';
    }

    // includes for any files to be displayed below anonymous right menu
    if (!file_exists($home . 'home_notice_' . $user_selected_language . '.html') && file_exists($home . 'home_notice.html') && file_get_contents($home . 'home_notice.html') != '') {
        echo '<div class="section">';
        if (file_exists($home . 'home_notice.html')) {
            include ($home . 'home_notice.html');
        } else {
            include ($home_old . 'home_notice.html');
        }
        echo '</div>';
    } elseif (file_exists($home . 'home_notice_' . $user_selected_language . '.html') && file_get_contents($home . 'home_notice_' . $user_selected_language . '.html') != '') {
        echo '<div class="section">';
        include($home . 'home_notice_' . $user_selected_language . '.html');
        echo '</div>';
    }
}

/**
 * 	Reacts on a failed login:
 * 	displays an explanation with
 * 	a link to the registration form.
 *
 * 	@version 1.0.1
 */
function handle_login_failed() {
    if (!isset($_GET['error'])) {
        $message = get_lang("InvalidId");
        if (api_is_self_registration_allowed()) {
            $message = get_lang("InvalidForSelfRegistration");
        }
    } else {
        switch ($_GET['error']) {
            case '':
                $message = get_lang('InvalidId');
                if (api_is_self_registration_allowed()) {
                    $message = get_lang('InvalidForSelfRegistration');
                }
                break;
            case 'account_expired':
                $message = get_lang('AccountExpired');
                break;
            case 'account_inactive':
                $message = get_lang('AccountInactive');
                break;
            case 'user_password_incorrect':
                $message = get_lang('InvalidId');
                break;
            case 'access_url_inactive':
                $message = get_lang('AccountURLInactive');
                break;
            case 'AdminNotifiedWrongLogin':
                $message = get_lang('AdminNotifiedWrongLogin');
        }
    }
    echo "<div id=\"login_fail\">" . $message . "</div>";
    echo '<script type="text/javascript">
            $(function() {
                // Handler for .ready() called.
                $("input[name=\"password\"]").val("");
            });
        </script>';
}

/**
 * 	Adds a form to let users login
 * 	@version 1.1
 */
function display_login_form() {
    $form = new FormValidator('formLogin');
    $form->addElement('text', 'login', get_lang('UserName'));
    $form->addElement('password', 'password', get_lang('Pass'));
    $form->addElement('style_submit_button', 'submitAuth', get_lang('langEnter'), array('class' => 'login'));
    $renderer = & $form->defaultRenderer();
    $renderer->setElementTemplate('<div ><label>{label}</label></div><div>{element}</div>');

    // Add register and lost password in login form - Bug #6821
    if (api_get_setting('allow_lostpassword') == 'true' || api_get_setting('allow_registration') == 'true') {
        $html_ul .=''; // Space due to that login button is floating
        $html_ul .= '<ul class="menulist nobullets" style="clear:both">';
        if (api_get_setting('allow_registration') <> 'false') {
            $html_ul.= '<li><a href="main/auth/inscription.php">' . get_lang('Reg') . '</a></li>';
        }
        if (api_get_setting('allow_lostpassword') == 'true') {
            $html_ul.= '<li><a href="main/auth/lostPassword.php">' . get_lang("LostPassword") . '</a></li>';
        }
        $html_ul.='</ul>';
    }
    
    // Add html element, Button
    $form->addElement('html', $html_ul);

    api_display_language_form(false, 'none;padding-left:5px',false ,'12px');
    $form->display();
    
    if (api_get_setting('openid_authentication') == 'true') {
        include_once 'main/auth/openid/login.php';
        echo '<div class="section">
                        <div class="sectiontitle">' . get_lang('OpenIdAuthentication') . '</div>
                        <div class="sectioncontent nobullets">';
        echo openid_form();
        echo '<br/><br/>
                        </div>';

        echo '</div>';
    }
    //Enter with cas for Paris5
    if (api_get_setting('cas_activate') == 'true') {

        echo '<div class="section" style="padding:8px;">
                        <div class="sectioncontent nobullets">';
        echo '<form action="main/auth/cas/logincas.php" method="post" id="loginform" name="formLogin">
                            <input type="hidden" name="isportalcas"  value="" />
                            <button type="submit" name="submitAuth" class="login">' . get_lang('CasLogin') . '</button>
                        </form>
                        <br/><br/>
                        </div>';

        echo '</div>';
    }
}

/**
 * Displays a link to the lost password section
 * Possible deprecated function
 */
function display_lost_password_info() {
    echo "<li><a href=\"main/auth/lostPassword.php\">" . get_lang("LostPassword") . "</a></li>";
}

/**
 * Display list of courses in a category.
 * (for anonymous users)
 *
 * @version 1.1
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University - refactoring and code cleaning
 */
function display_anonymous_course_list() {
    $ctok = $_SESSION['sec_token'];
    $stok = Security::get_token();

    //init
    $user_identified = (api_get_user_id() > 0 && !api_is_anonymous());
    $web_course_path = api_get_path(WEB_COURSE_PATH);
    $category = Database::escape_string($_GET['category']);
    global $setting_show_also_closed_courses;

    // Database table definitions
    $main_course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
    $main_category_table = Database :: get_main_table(TABLE_MAIN_CATEGORY);

    $platformLanguage = api_get_setting('platformLanguage');

    //get list of courses in category $category
    $sql_get_course_list = "SELECT * FROM $main_course_table cours
								WHERE category_code = '" . Database::escape_string($_GET["category"]) . "'
								ORDER BY title, UPPER(visual_code)";

    //showing only the courses of the current access_url_id
    global $_configuration;
    if ($_configuration['multiple_access_urls'] == true) {
        $url_access_id = api_get_current_access_url_id();
        if ($url_access_id != -1) {
            $tbl_url_rel_course = Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_COURSE);
            $sql_get_course_list = "SELECT * FROM $main_course_table as course INNER JOIN $tbl_url_rel_course as url_rel_course
					ON (url_rel_course.course_code=course.code)
					WHERE access_url_id = $url_access_id AND category_code = '" . Database::escape_string($_GET["category"]) . "' ORDER BY title, UPPER(visual_code)";
        }
    }

    //removed: AND cours.visibility='".COURSE_VISIBILITY_OPEN_WORLD."'
    $sql_result_courses = Database::query($sql_get_course_list, __FILE__, __LINE__);

    while ($course_result = Database::fetch_array($sql_result_courses)) {
        $course_list[] = $course_result;
    }

    $platform_visible_courses = '';
    // $setting_show_also_closed_courses
    if ($user_identified) {
        if ($setting_show_also_closed_courses) {
            $platform_visible_courses = '';
        } else {
            $platform_visible_courses = "  AND (t3.visibility='" . COURSE_VISIBILITY_OPEN_WORLD . "' OR t3.visibility='" . COURSE_VISIBILITY_OPEN_PLATFORM . "' )";
        }
    } else {
        if ($setting_show_also_closed_courses) {
            $platform_visible_courses = '';
        } else {
            $platform_visible_courses = "  AND (t3.visibility='" . COURSE_VISIBILITY_OPEN_WORLD . "' )";
        }
    }
    $sqlGetSubCatList = "
				SELECT t1.name,t1.code,t1.parent_id,t1.children_count,COUNT(DISTINCT t3.code) AS nbCourse
				FROM $main_category_table t1
				LEFT JOIN $main_category_table t2 ON t1.code=t2.parent_id
				LEFT JOIN $main_course_table t3 ON (t3.category_code=t1.code $platform_visible_courses)
				WHERE t1.parent_id " . (empty($category) ? "IS NULL" : "='$category'") . "
				GROUP BY t1.name,t1.code,t1.parent_id,t1.children_count ORDER BY t1.tree_pos, t1.name";


    //showing only the category of courses of the current access_url_id
    global $_configuration;
    if ($_configuration['multiple_access_urls'] == true) {
        $url_access_id = api_get_current_access_url_id();
        if ($url_access_id != -1) {
            $tbl_url_rel_course = Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_COURSE);
            $sqlGetSubCatList = "
				SELECT t1.name,t1.code,t1.parent_id,t1.children_count,COUNT(DISTINCT t3.code) AS nbCourse
				FROM $main_category_table t1
				LEFT JOIN $main_category_table t2 ON t1.code=t2.parent_id
				LEFT JOIN $main_course_table t3 ON (t3.category_code=t1.code $platform_visible_courses)
				INNER JOIN $tbl_url_rel_course as url_rel_course
					ON (url_rel_course.course_code=t3.code)
				WHERE access_url_id = $url_access_id AND t1.parent_id " . (empty($category) ? "IS NULL" : "='$category'") . "
				GROUP BY t1.name,t1.code,t1.parent_id,t1.children_count ORDER BY t1.tree_pos, t1.name";
        }
    }

    $resCats = Database::query($sqlGetSubCatList, __FILE__, __LINE__);
    $thereIsSubCat = false;
    if (Database::num_rows($resCats) > 0) {
        $htmlListCat = "<h3 class=\"tablet_title\" >" . get_lang("CatList") . "</h3>" . "<ul style=\"list-style-type:none;margin-left:0px;padding-left:10px;\">";
        while ($catLine = Database::fetch_array($resCats)) {
            if ($catLine['code'] != $category) {

                $category_has_open_courses = category_has_open_courses($catLine['code']);
                if ($category_has_open_courses) {
                    //the category contains courses accessible to anonymous visitors
                    $htmlListCat .= "<li>";
                    $htmlListCat .= "<a href=\"" . api_get_self() . "?category=" . $catLine['code'] . "\">" . Display::return_icon('pixel.gif', $catLine['name'], array('class' => 'actionplaceholdericon actionnewfolder')) . ' ' . $catLine['name'] . "</a>";
                    if (api_get_setting('show_number_of_courses') == 'true') {
                        $htmlListCat .= " (" . $catLine['nbCourse'] . " " . get_lang("Courses") . ")";
                    }
                    $htmlListCat .= "</li>\n";
                    $thereIsSubCat = true;
                } elseif ($catLine['children_count'] > 0) {
                    //the category has children, subcategories
                    $htmlListCat .= "<li>";
                    $htmlListCat .= "<a href=\"" . api_get_self() . "?category=" . $catLine['code'] . "\">" . Display::return_icon('pixel.gif', $catLine['name'], array('class' => 'actionplaceholdericon actionnewfolder')) . ' ' . $catLine['name'] . "</a>";
                    $htmlListCat .= "</li>\n";
                    $thereIsSubCat = true;
                }
                /*                 * **********************************************************************
                  end changed code to eliminate the (0 courses) after empty categories
                 * ********************************************************************** */ elseif (api_get_setting('show_empty_course_categories') == 'true') {
                    $htmlListCat .= "<li>";
                    $htmlListCat .= Display::return_icon('pixel.gif', $catLine['name'], array('class' => 'actionplaceholdericon actionnewfolder')) . ' ' . $catLine['name'];
                    $htmlListCat .= "</li>\n";
                    $thereIsSubCat = true;
                } //else don't set thereIsSubCat to true to avoid printing things if not requested
            } else {
                $htmlTitre = "<p>";
                if (api_get_setting('show_back_link_on_top_of_tree') == 'true') {
                    $htmlTitre .= "<a href=\"" . api_get_self() . "\">" . "&lt;&lt; " . get_lang("BackToHomePage") . "</a>";
                }
                if (!is_null($catLine['parent_id']) || (api_get_setting('show_back_link_on_top_of_tree') <> 'true' && !is_null($catLine['code']))) {
                    $htmlTitre .= "<a href=\"" . api_get_self() . "?category=" . $catLine['parent_id'] . "\">" . "&lt;&lt; " . get_lang('Up') . "</a>";
                }
                $htmlTitre .= "</p>\n";
                if ($category != "" && !is_null($catLine['code'])) {
                    $htmlTitre .= "<h3 class=\"tablet_title\">" . Display::return_icon('pixel.gif', $catLine['name'], array('class' => 'actionplaceholdericon actionnewfolder')) . ' ' . $catLine['name'] . "</h3>\n";
                } else {
                    $htmlTitre .= "<h3 class=\"tablet_title\">" . get_lang("Categories") . "</h3>\n";
                }
            }
        }
        $htmlListCat .= "</ul>\n";
    }
    echo $htmlTitre;
    if ($thereIsSubCat) {
        echo $htmlListCat;
    }
    while ($categoryName = Database::fetch_array($resCats)) {
        echo "<h3 class=\"tablet_title\">", $categoryName['name'], "</h3>\n";
    }
    $numrows = Database::num_rows($sql_result_courses);
    $courses_list_string = '';
    $courses_shown = 0;
    if ($numrows > 0) {
        if ($thereIsSubCat) {
            $courses_list_string .= "<hr size=\"1\" noshade=\"noshade\">\n";
        }
        $courses_list_string .= "<h3 class=\"tablet_title\" >" . get_lang("CourseList") . "</h3>\n" . "<ul>\n";

        if (api_get_user_id()) {
            $courses_of_user = get_courses_of_user(api_get_user_id());
        }

        foreach ($course_list as $course) {
            // $setting_show_also_closed_courses

            if ($setting_show_also_closed_courses == false) {
                // if we do not show the closed courses
                // we only show the courses that are open to the world (to everybody)
                // and the courses that are open to the platform (if the current user is a registered user
                if (($user_identified && $course['visibility'] == COURSE_VISIBILITY_OPEN_PLATFORM) || ($course['visibility'] == COURSE_VISIBILITY_OPEN_WORLD)) {
                    $courses_shown++;
                    $courses_list_string .= "<li>\n";
                    $courses_list_string .= "<a href=\"" . $web_course_path . $course['directory'] . "/\">" . $course['title'] . "</a><br />";
                    if (api_get_setting('display_coursecode_in_courselist') == 'true') {
                        $courses_list_string .= $course['visual_code'];
                    }
                    if (api_get_setting('display_coursecode_in_courselist') == 'true' && api_get_setting('display_teacher_in_courselist') == 'true') {
                        $courses_list_string .= ' - ';
                    }
                    if (api_get_setting('display_teacher_in_courselist') == 'true') {
                        $courses_list_string .= $course['tutor_name'];
                    }
                    if (api_get_setting('show_different_course_language') == 'true' && $course['course_language'] != api_get_setting('platformLanguage')) {
                        $courses_list_string .= ' - ' . $course['course_language'];
                    }
                    $courses_list_string .= "</li>\n";
                }
            }
            // we DO show the closed courses.
            // the course is accessible if (link to the course homepage)
            // 1. the course is open to the world (doesn't matter if the user is logged in or not): $course['visibility'] == COURSE_VISIBILITY_OPEN_WORLD)
            // 2. the user is logged in and the course is open to the world or open to the platform: ($user_identified && $course['visibility'] == COURSE_VISIBILITY_OPEN_PLATFORM)
            // 3. the user is logged in and the user is subscribed to the course and the course visibility is not COURSE_VISIBILITY_CLOSED
            // 4. the user is logged in and the user is course admin of te course (regardless of the course visibility setting)
            // 5. the user is the platform admin api_is_platform_admin()
            //
			else {
                $courses_shown++;
                $courses_list_string .= "<li>\n";
                if ($course['visibility'] == COURSE_VISIBILITY_OPEN_WORLD
                        || ($user_identified && $course['visibility'] == COURSE_VISIBILITY_OPEN_PLATFORM)
                        || ($user_identified && key_exists($course['code'], $courses_of_user) && $course['visibility'] != COURSE_VISIBILITY_CLOSED)
                        || $courses_of_user[$course['code']]['status'] == '1'
                        || api_is_platform_admin()) {
                    $courses_list_string .= "<a href=\"" . $web_course_path . $course['directory'] . "/\">";
                }
                $courses_list_string .= $course['title'];
                if ($course['visibility'] == COURSE_VISIBILITY_OPEN_WORLD
                        || ($user_identified && $course['visibility'] == COURSE_VISIBILITY_OPEN_PLATFORM)
                        || ($user_identified && key_exists($course['code'], $courses_of_user) && $course['visibility'] != COURSE_VISIBILITY_CLOSED)
                        || $courses_of_user[$course['code']]['status'] == '1'
                        || api_is_platform_admin()) {
                    $courses_list_string .= "</a><br />";
                }
                if (api_get_setting('display_coursecode_in_courselist') == 'true') {
                    $courses_list_string .= $course['visual_code'];
                }
                if (api_get_setting('display_coursecode_in_courselist') == 'true' && api_get_setting('display_teacher_in_courselist') == 'true') {
                    $courses_list_string .= ' - ';
                }
                if (api_get_setting('display_teacher_in_courselist') == 'true') {
                    $courses_list_string .= $course['tutor_name'];
                }
                if (api_get_setting('show_different_course_language') == 'true' && $course['course_language'] != api_get_setting('platformLanguage')) {
                    $courses_list_string .= ' - ' . $course['course_language'];
                }
                if (api_get_setting('show_different_course_language') == 'true' && $course['course_language'] != api_get_setting('platformLanguage')) {
                    $courses_list_string .= ' - ' . $course['course_language'];
                }
                // We display a subscription link if
                // 1. it is allowed to register for the course and if the course is not already in the courselist of the user and if the user is identiefied
                // 2
                if ($user_identified && !key_exists($course['code'], $courses_of_user)) {
                    if ($course['subscribe'] == '1') {
                        $courses_list_string .= "<form action=\"main/auth/courses.php?action=subscribe&amp;category=" . $_GET['category'] . "\" method=\"post\">";
                        $courses_list_string .= '<input type="hidden" name="sec_token" value="' . $stok . '">';
                        $courses_list_string .= "<input type=\"hidden\" name=\"subscribe\" value=\"" . $course['code'] . "\" />";
                        $courses_list_string .= "<input type=\"image\" name=\"unsub\" src=\"main/img/enroll.gif\" alt=\"" . get_lang("Subscribe") . "\" />" . get_lang("Subscribe") . "</form>";
                    } else {
                        $courses_list_string .= '<br />' . get_lang("SubscribingNotAllowed");
                    }
                }
                $courses_list_string .= "</li>\n";
            }
        }
        $courses_list_string .= "</ul>\n";
    } else {
        // echo "<blockquote>",get_lang('_No_course_publicly_available'),"</blockquote>\n";
    }
    if ($courses_shown > 0) { //only display the list of courses and categories if there was more than
        // 0 courses visible to the world (we're in the anonymous list here)
        echo $courses_list_string;
    }
    if ($category != '') {
        if (api_get_setting('show_back_link_on_top_of_tree') == 'true') {
            $sql = "SELECT parent_id FROM $main_category_table WHERE code = '$category'";
            $result = Database::query($sql, __FILE__, __LINE__);
            if (Database::num_rows($result) > 0 && !is_null($category_up = Database::result($result, 0))) {
                echo "<p><a href=\"" . api_get_self() . "?category=" . $category_up . "\">&nbsp;&nbsp;&lt;&lt; " . get_lang('Up') . "</a></p>\n";
            }
        }
        echo "<p>", "<a href=\"" . api_get_self() . "\"> ", Display :: return_icon('pixel.gif', get_lang('BackToHomePage'), array('class' => 'actionplaceholdericon actionprev_navigation')), get_lang("BackToHomePage"), "</a>", "</p>\n";
    }
}

/**
 * retrieves all the courses that the user has already subscribed to
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @param int $user_id: the id of the user
 * @return array an array containing all the information of the courses of the given user
 */
function get_courses_of_user($user_id) {
    $table_course = Database::get_main_table(TABLE_MAIN_COURSE);
    $table_course_user = Database::get_main_table(TABLE_MAIN_COURSE_USER);

    // Secondly we select the courses that are in a category (user_course_cat <> 0) and sort these according to the sort of the category
    $user_id = intval($user_id);
    $sql_select_courses = "SELECT course.code k, course.visual_code  vc, course.subscribe subscr, course.unsubscribe unsubscr,
								course.title i, course.tutor_name t, course.db_name db, course.directory dir, course_rel_user.status status,
								course_rel_user.sort sort, course_rel_user.user_course_cat user_course_cat
								FROM    $table_course       course,
										$table_course_user  course_rel_user
								WHERE course.code = course_rel_user.course_code
								AND   course_rel_user.user_id = '" . $user_id . "'
								ORDER BY course_rel_user.sort ASC";
    $result = Database::query($sql_select_courses, __FILE__, __LINE__);
    while ($row = Database::fetch_array($result)) {
        // we only need the database name of the course
        $courses[$row['k']] = array('db' => $row['db'], 'code' => $row['k'], 'visual_code' => $row['vc'], 'title' => $row['i'], 'directory' => $row['dir'], 'status' => $row['status'], 'tutor' => $row['t'], 'subscribe' => $row['subscr'], 'unsubscribe' => $row['unsubscr'], 'sort' => $row['sort'], 'user_course_category' => $row['user_course_cat']);
    }
    return $courses;
}

/**
 * Enter description here...
 *
 */
function display_create_course_link_tablet() {
    echo "<a href=\"main/create_course/add_course.php\">" . Display::return_icon('pixel.gif', get_lang('CourseCreate'), array('class' => 'homepage_button homepage_create_course', 'align' => 'middle','style'=>'float:left')).'<div class="tablet_section_left_link">'.get_lang('CourseCreate') . "</div></a><br/>";
}

/**
 * Enter description here...
 *
 */
function display_edit_course_list_links_tablet() {
    echo "<a href=\"main/auth/courses.php\">" . Display::return_icon('pixel.gif', get_lang('SortMyCourses'), array('class' => 'homepage_button homepage_catalogue', 'align' => 'middle','style'=>'float:left')).'<div class="tablet_section_left_link">'. get_lang('SortMyCourses') . "</div></a>";
}

/**
 * Trainers manual, Video tutorials and Trainers training is displayed.
 * 
 * @author Ricardo Garcia Rodriguez <master.ojitos@gmail.com>
 */
function display_trainer_links_tablet() {
    $user_selected_language = api_get_interface_language();
    if (!isset($user_selected_language)) {
        $user_selected_language = api_get_setting('platformLanguage');
    }
    $trainers_training_link = 'http://www.dokeos.com/en/training/';
    if ($user_selected_language == "french") {
        $trainers_training_link = 'http://www.dokeos.com/fr/training/';
    }
    echo "<a href=\"http://www.dokeos.com/en/trainer-manual/\" target=\"_blank\">" . Display::return_icon('pixel.gif', get_lang('TrainersManual'), array('class' => 'homepage_button homepage_trainers_manual', 'align' => 'middle','style'=>'float:left')) . '<div class="tablet_section_left_link">'.get_lang('TrainersManual') . "</div></a>";
    echo "<a href=\"http://www.dokeos.com/en/video-tutorials/\" target=\"_blank\">" . Display::return_icon('pixel.gif', get_lang('VideoTutorials'), array('class' => 'homepage_button homepage_video_tutorials', 'align' => 'middle','style'=>'float:left')) . '<div class="tablet_section_left_link">'.get_lang('VideoTutorials') . "</div></a>";
    echo "<a href=\"" . $trainers_training_link . "\" target=\"_blank\">" . Display::return_icon('pixel.gif', get_lang('TrainersTraining'), array('class' => 'homepage_button homepage_trainers_training', 'align' => 'middle','style'=>'float:left')) . '<div class="tablet_section_left_link">'.get_lang('TrainersTraining') . "</div></a>";
}
