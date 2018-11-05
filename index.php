<?php
/* For licensing terms, see /license.txt */

// only this script should have this constant defined. This is used to activate the javascript that
// gives the login name automatic focus in header.inc.html.
/** @todo Couldn't this be done using the $HtmlHeadXtra array? */
define('DOKEOS_HOMEPAGE', true);

// the language file
$language_file = array('courses', 'index', 'admin');

/* Flag forcing the 'current course' reset, as we're not inside a course anymore */
// maybe we should change this into an api function? an example: Coursemanager::unset();
$cidReset = true;

global $language_interface;
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
require_once (api_get_path(LIBRARY_PATH). 'language.lib.php');
require_once api_get_path(LIBRARY_PATH) . 'sublanguagemanager.lib.php';
require_once (api_get_path(LIBRARY_PATH). 'timezone.lib.php');

// for shopping cart & catalog
/*require_once api_get_path(SYS_PATH) . 'main/core/model/ecommerce/EcommerceCatalog.php';
require_once api_get_path(SYS_PATH) . 'main/core/controller/shopping_cart/shopping_cart_controller.php';
require_once api_get_path(SYS_PATH) . 'main/application/suiteManager/models/PricingModel.php';*/

if (is_dir(api_get_path(SYS_PATH).'PRO')) {
    if (isset($_GET['action']) && $_GET['action'] == 'makeitpro') {
        header('Location: ' . api_get_path(WEB_PATH) . 'PRO/makeitpro.php?from=installdone');
        exit;
    }
}
define('PROMOTED', 1);
define('ACTIVE',1);
$theme_custom_index_page = array('orkyn_tablet');
$stylesheet = api_get_setting('stylesheets');
$is_customized = in_array($stylesheet, $theme_custom_index_page);
if ($is_customized) {
    header('Location: custom_index.php');
    exit;
}  

/*$theme_custom_index_page = array('gsf_theme');
$stylesheet = api_get_setting('stylesheets');
$is_customized = in_array($stylesheet, $theme_custom_index_page);
if ($is_customized) {
    echo ' <style> .back-top, .back-bottom { display:block !important; } </style>';
};*/

//$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-1.5.1.min.js" ></script>';
//Code changed like this for testing.
$htmlHeadXtra[] = '<link type="text/css" href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/thiagosf-SkitterSlideshow/css/skitter.styles.css" rel="stylesheet" />';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/thiagosf-SkitterSlideshow/js/jquery.skitter.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/general-functions.js" ></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.form.js"></script>';
$htmlHeadXtra[] = '<link type="text/css" href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui/css/ui-lightness/jquery-ui-1.8.1.custom.css" rel="stylesheet" />';
$htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/chosen/chosen.css"/>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/chosen/chosen.jquery.min.js" ></script>';
$htmlHeadXtra[] = '<link rel="stylesheet" href="'.api_get_path(WEB_CODE_PATH).'css/'.api_get_setting('stylesheets').'/flexslider.css" type="text/css" media="screen" />';

$htmlHeadXtra[] = '<style type="text/css">
    .flex-caption {
      width: 100%;
      padding: 12px;
      left: 0;
      bottom: 0;
      background: #808080;
      color: #fff;
      text-shadow: 0 -1px 0 rgba(0,0,0,.3);
      font-size: 14px;
      line-height: 18px;
	  margin:0px !important;
    }
    li.css a {
      border-radius: 0;
    }
	.section {
	      padding: 6px 10px 10px !important;
	}
  </style>';

$htmlHeadXtra[] = '<script defer src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.flexslider.js"></script>

  <script type="text/javascript">    
    $(window).load(function(){
      $(".flexslider").flexslider({
        animation: "slide",		
        start: function(slider){
          $("body").removeClass("loading");
        }
      });
    });
  </script>';

$slides_management_table = Database :: get_main_table(TABLE_MAIN_SLIDES_MANAGEMENT);
$rs = Database::query("SELECT * FROM $slides_management_table LIMIT 1");
$row = Database::fetch_array($rs);
$show_slide = $row['show_slide'];
$slide_speed = ($row['slide_speed'] == 0) ? 1 : $row['slide_speed'];
$slide_speed_millisec = intval($slide_speed) * 1000;

$htmlHeadXtra[] = '
<script type="text/javascript">
function validateSaas(){
var is_saas_version = "'.api_is_sas_version().'";
    if (is_saas_version){
    var left_days = '.api_get_portal_days_left().';                
                        if(left_days <= 0){ 
                            $("form[name=\"formLogin\"]").submit(function(e){
                                e.preventDefault();      
                            });
                            var user = $("input[name=\"login\"]").val();
                            var password = $("input[name=\"password\"]").val();
                            var divToShow = "";
                            $.ajax({
                                url: "'.api_get_path(WEB_AJAX_PATH).'user_manager.ajax.php?a=get_status&user="+user,
                                success: function(data){               
                                    if(data == 1){                     
                                       $("#linkToSendMessage").attr("href","/main/index.php?module=suiteManager&cmd=Pricing&func=index&u="+user) 
                                       divToShow = $("#portalExpiredMessageAdmin").attr("id");                     
                                    }
                                    else{                   
                                       divToShow = $("#portalExpiredMessageOthers").attr("id");  
                                    } 
                                    divToShow = "#"+divToShow;
                                    
                                    $(divToShow).dialog({
                                        resizable:false,
                                        closeOnEscape: false,
                                        width: 625,
                                        heigth: 140,
                                        modal:true,                    
                                        title:"'.get_lang('Info').'" ,
                                        open: function(event, ui) {  
                                            $(".ui-dialog-titlebar-close").css("width","0px");
                                            $(".ui-dialog-titlebar-close").html("<span style=\"float:right;margin-right:5px;\"></span>");  											
                                        }
                                    });//end dialog
                                    $(".ui-corner-all").removeClass("ui-dialog-titlebar-close"); //remove close button
                                        
                                }//end success
                           });//ajax end   
                       }//end if
        }//end if
                       
}//end validateSaas function

            $(function() {
                $("#accordion").accordion({
                    heightStyle: "content",
                    collapsible: true

                });
            }); 
</script>
                                
<script type="text/javascript">
$(document).ready( function() {

	//shopcart
var height_logo = $("#logo_home_banner").height();
$("#cart").css({"top":height_logo + 45 , "right":"8px"});

    var isNodePage = "'.Database::escape_string($_GET['nodeId']).'";
    var isLoggedOut = "'.  api_is_anonymous().'";
    $("#menu").children("div").each(function(index, dom) {
        if($(dom).html() === "" || isNodePage !== "")
            $(dom).hide();
    });
    if ((isNodePage !== "")&&(isLoggedOut == "")) {       
        $("#content_with_menu").css({width:"97%",padding:"0 15px 15px",float:"none"});
    }
    else if((isLoggedOut)&&("'.isset($_GET['nodeId']).'")){
        $("#menu").css("display","none");
        $("#content_with_menu").css({width:"97%",padding:"15px 15px 15px",float:"none"});
    }
if((navigator.userAgent.indexOf(\'MSIE 8.0\') > -1)||(navigator.userAgent.indexOf(\'MSIE 9.0\') > -1)){
    $("embed[src~=\'youtube\']").parent().attr("style","height:800px");
}     
     
$(".subscribeForm").ajaxForm(function(){
    
    success: {    
        location.reload();
        $("#confirmationSubs").val("sent");
        document.confirmationForm.submit();
    }
    
 });

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
        interval: ' . $slide_speed_millisec . '
    });
  }

  
  
});
 
</script>
<style  type="text/css">
.smallwhite {
    margin: 0px 15px !important;
}
/*.img_catalog {
width:25%;
}*/
#page_template {
width:100% !important;
}
input[type=checkbox] {
	display:none !important;
}
input[type=checkbox] + label {
    position: relative;
    padding-left: 25px;
    display: block;
    cursor: pointer; }
    /* line 123, ../sass/partials/objects/_forms.scss */
    input[type=checkbox] + label:before {
      content: "";
      font-family: "icomoon";
      width: 15px;
      height: 15px;
      position: absolute;
      left: 0;
      top: 2px;
      border: 1px solid #d1d1d1;
	  display: block !important; }
#back-link {
  float: right;
    font-size: 16px;
    line-height: 42px;
    margin-right: 10px;
    text-decoration: underline;
}
.desCusCatalogue {
  position: relative !important;
  height: auto !important;
}
</style>
 
';

if (intval($_GET['popup']) > 0) {
    $htmlHeadXtra[] = '
        <script type="text/javascript">
                    $(function() {
                        displayProductPopup();                
                    });                     
                    function displayProductPopup() {
                        var procesa="/main/index.php?module=ecommerce&cmd=Sessionlist&func=getItemDetail&id="+'.intval($_GET['popup']).';
                        $.getJSON(procesa,
                        function(json){
                            var backLink = "/index.php";
                            var item_type = "course";
                            if (json.item_type == 3) {
                                var item_type = "session";
                            }
                            else if (json.item_type == 1) {
                                var item_type = "module";
                            }
			//$(".catalog__content .content__title").html(json.title);
			$(".catalog__content").html(json.description);

                            /*$("#divDescription").html("<div class=\"desCusCatalogue\"><p>'.get_lang('EcommerceLearnMore').'</p><span rel=\""+item_type+"\" id=\"shoppingCartCatalog\"></span><div style=\"width:100%;\" class=\"titleDescription\">"+json.title+"</div><p>"+json.description+"</p></div><div class=\"CustomButtonCatalog\">"+json.button+"<a href=\""+backLink+"\" id=\"back-link\">'.get_lang('Return').'</a></div>");*/
                        });
                        return false;
                    }

        </script>';
}

$loginFailed = isset($_GET['loginFailed']) ? true : isset($loginFailed);
$setting_show_also_closed_courses = api_get_setting('show_closed_courses') == 'true';

// the section (for the tabs)
//var_dump($_SERVER['REQUEST_URI']);

$this_section = SECTION_CAMPUS;
if(isset($_GET['nodeId'])){
    $id= Security::remove_XSS($_GET['nodeId']);
    $sql = 'SELECT menu_link_id FROM '.TABLE_MAIN_NODE.' WHERE id='.  $id;
    $res = Database::query($sql);
    $row = Database::fetch_row($res);
    $menu_link_id = $row[0];    
    $sqlWeight = 'SELECT weight FROM '.TABLE_MAIN_MENU_LINK.' WHERE id = '.$menu_link_id;
    $resWeight = Database::query($sqlWeight);
    $rowWeight = Database::fetch_row($resWeight);
    $this_section = $rowWeight[0];
}
// Check if we have a CSS with tablet support
$css_info = array();
$css_info = api_get_css_info();
$css_type = !is_null($css_info['type']) ? $css_info['type'] : 'tablet';

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
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.validate.js" ></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_CODE_PATH).'css/mp_tablet/js/login.js" ></script>';
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

// the header
Display :: display_header('', 'dokeos');

    
  if(isset($_SESSION["display_confirmation_message"])&&($_POST['confirmationSubs']!= '')){
           Display :: display_confirmation_message2($_SESSION["display_confirmation_message"],false,true);
           unset($_SESSION["display_confirmation_message"]);
  }
  echo '<form action='.  api_get_self().' name="confirmationForm" method= "POST">';
  echo '<input id="confirmationSubs" name="confirmationSubs" type="hidden" value= ""/>';
  echo '</form>';
  
//echo '<div id="content" class="maxcontent" /*style="background: url(main/css/mp_tablet/img/connexion-banniere.jpg) no-repeat left bottom 39px;"*/>';

echo '<div class="">';


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
$show_menu = true;
if ((api_is_allowed_to_create_course() || api_is_session_admin()) && ($_SESSION["studentview"] != "studentenview")) {
    $show_menu = true;
}
if (!$show_menu) {
    if (!($_user['user_id']) || api_is_anonymous($_user['user_id'])) {
        
    } else {
        $display_open_course = display_open_course(true);
        $show_opened_courses = api_get_setting('show_opened_courses');
        $display_open_course = isset($display_open_course) ? $display_open_course : false;
        $show_opened_courses = isset($show_opened_courses) ? $show_opened_courses : false;

        $check1 = ($display_open_course AND $show_opened_courses == 'true') ? (false) : (true);
    }
}
$width = ($check1) ? "style='margin: auto!important;float:none;'" : "";
//echo '<div class="main__content content" ' . $width . ' style="float:right">';

/*if (api_is_platform_admin()) {
    echo '<div id="edit_homepage_bloc" style="float:right">
			<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/configure_homepage.php">' . Display::return_icon('pixel.gif', get_lang('EditPublicPages'), array('class' => 'actionplaceholdericon actionedit', 'align' => 'middle')) . get_lang('EditPublicPages') . '</a>
          </div>';
}*/


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


// Display courses and category list
//if (ShoppingCartController::create()->isShoppingCartEnabled()) {
    echo '<script type="text/javascript" src="' . api_get_path(WEB_PATH) . 'main/appcore/library/jquery/jPaginate/jquery.paginate.js"></script>';
    echo '<link type="text/css" rel="stylesheet" href="' . api_get_path(WEB_PATH) . 'main/appcore/library/jquery/jPaginate/css/style.css"/>';
    echo '<script type="text/javascript">
    function listCategory(id_category, name)
    {
        window.location.href = "index.php?module=ecommerce&id_category="+id_category+"&name_category="+name;
    
        //$(".ecommerce_text_category").removeClass("active");        
        //$("#cat-"+parseInt(id_category)).addClass("active");

        //var cant;
        //$("#divPaginator").empty();
        //$("#divPaginator").removeAttr("style");
        //$("#divPaginator").removeAttr("class");
        //$("#ElistCatalog").empty();
        //$("#divPaginator").paginate({
        //                count 		: cantidad,
        //                start 		: 1,
        //                display                 : 10,
        //                border			: false,
        //                text_color  		: "#888",
        //                background_color    	: "#EEE",	
        //                text_hover_color  	: "black",
        //                background_hover_color	: "#CFCFCF",
        //                onChange                : function(page){
        //                                                    $.ajax({
        //                                                        type: "POST",
        //                                                        url: "main/index.php?module=ecommerce&cmd=Sessionlist&func=getCoursePaginator&page="+page+"&count=10&id_category="+id_category,
        //                                                        //data: datos,
        //                                                        dataType: "json",
        //                                                        success: function(data){
        //                                                        $("#ElistCatalog").empty();
        //                                                        $.each(data, function(i, item) {
        //                            var html = "<table style=\'margin-bottom:20px;\' class=\'back_table_catalog\'><tr>";
        //                            var classbox = "";
        //                            if(item.description == false) { item.description = ""; }
        //                                if(item.image != "../../../main/img/pixel.gif"){
        //                                var mt = "";
        //                                if(item.h == "100px"){
        //                                    mt = "margin-top:30px"
        //                                }else{
        //                                    mt = "margin-top:6px";
        //                                }
        //                                    html+= "<td class=\'box_catalog\' style=\'text-align:center;padding:0px; width:200px;height:150px;\'><img style=\'"+mt+";height: "+item.h+";width: "+item.w+";\'  src=\'"+item.image+" \' /></td>";
        //                                    classbox = "style=\'height:130px; width:66.5%!important; margin-left:10px!important;\'";
        //                                }                                        
        //                                html+= "<td class=\'box_catalog2\' "+classbox+"><table width=\'100%\'><tr style=\'height:70px;\'><td style=\'vertical-align:top; padding-left:10px;width:400px;\'><strong style=\'font-zise:12px;\'>"+item.title+"</strong><br />"+item.description+"</td></tr><tr style=\'height:30px\'><td style=\'vertical-align:top; padding-left:10px;width:400px;color:#000;\'><strong>"+item.langprice+": </strong>"+item.priceinfo+" &nbsp;&nbsp;&nbsp;<strong>'.get_lang('Access').': </strong>"+item.durationinfo+"</td></tr><tr  style=\'height:20px;\'><td style=\'text-align:right;\'><div style=\'float:right;\'><table rel=\'"+item.type+"\' id=\'shoppingCartCatalog\'><tr><td><a href=\'#\' class=\'addToCartCourse\' id=\'"+item.id+"\' onClick=\'return viewDetail(this);\'><span>"+item.seemore+"</span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=\'course_catalog_container\' rel=\'"+item.codeitem+"\'><a class=\'addToCartCourse addToCartCourseClick\' href=\'#\'><span>"+item.addtocart+"</span></a></span></td></tr></table></div></td></tr></table></td></tr></table>";
        //                            $("#ElistCatalog").append(html);
        //                                                        });
        //                                                        },
        //                                                        timeout:80000
        //                                                    });
        //                                        }
        //        });

        //       procesa="main/index.php?module=ecommerce&cmd=Sessionlist&func=getCoursePaginator&page=1&count=10&id_category="+id_category;
        //        $.getJSON(procesa,
        //        function(json){
        //                $.each(json, function(i, item) {
        //                            var html = "<table style=\'margin-bottom:20px;\' class=\'back_table_catalog\'><tr>";
        //                            var classbox = "";
        //                            if(item.description == false) { item.description = ""; }
        //                                if(item.image != "../../../main/img/pixel.gif"){
        //                                var mt = "";
        //                                if(item.h == "100px"){
        //                                    mt = "margin-top:30px"
        //                                }else{
        //                                    mt = "margin-top:6px";
        //                                }
        //                                html+= "<td class=\'box_catalog\' style=\'text-align:center;padding:0px; width:200px;height:150px;\' >";
        //                                html+= "<a href=\'javascript:void(0);\' id=\'"+item.id+"\' onClick=\'return viewDetail(this);\'>";
        //                                html+= "<img style=\'"+mt+";height: "+item.h+";width: "+item.w+";\' src=\'"+item.image+" \' />";
        //                                html+= "</a>";
        //                                html+= "</td>";
        //                                    classbox = "style=\'height:130px; width:66.5%!important; margin-left:10px!important;\'";
        //                                }
        //                                if(item.chr_type_cost==0){
        //                                    html+= "<td class=\'box_catalog2\' "+classbox+"><table width=\'100%\'><tr style=\'height:70px;\'><td style=\'vertical-align:top; padding-left:10px;width:400px;\'><strong style=\'font-zise:12px;\'>"+item.title+"</strong><br />"+item.summary+"</td></tr><tr style=\'height:30px\'><td style=\'vertical-align:top; padding-left:10px;width:400px;color:#000;\'><strong>"+item.langprice+": </strong>'.get_lang('Free').' &nbsp;&nbsp;&nbsp;<strong>'.get_lang('Access').': </strong>"+item.durationinfo+"</td></tr><tr  style=\'height:20px;\'><td style=\'text-align:right;\'><div style=\'float:right;\'><table rel=\'"+item.type+"\' id=\'shoppingCartCatalog\'><tr><td>";
        //                                    html+= "<a href=\'#\' style=\'text-decoration: underline;\' id=\'"+item.id+"\' onClick=\'return viewDetail(this);\'><span>"+item.seemore+"</span></a>";
        //                                    html+= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        //                                    html+= "<span class=\'course_catalog_container\' rel=\'"+item.codeitem+"\'><a class=\'addToCartCourseFree\' style=\'margin-top: -15px;\' href=\'#\'><span>"+item.addtocartfree+"</span></a></span>";
        //                                } else {
        //                                    html+= "<td class=\'box_catalog2\' "+classbox+"><table width=\'100%\'><tr style=\'height:70px;\'><td style=\'vertical-align:top; padding-left:10px;width:400px;\'><strong style=\'font-zise:12px;\'>"+item.title+"</strong><br />"+item.summary+"</td></tr><tr style=\'height:30px\'><td style=\'vertical-align:top; padding-left:10px;width:400px;color:#000;\'><strong>"+item.langprice+": </strong>"+item.priceinfo+" &nbsp;&nbsp;&nbsp;<strong>'.get_lang('Access').': </strong>"+item.durationinfo+"</td></tr><tr  style=\'height:20px;\'><td style=\'text-align:right;\'><div style=\'float:right;\'><table rel=\'"+item.type+"\' id=\'shoppingCartCatalog\'><tr><td>";
        //                                    html+= "<a href=\'#\' style=\'text-decoration: underline;\' id=\'"+item.id+"\' onClick=\'return viewDetail(this);\'><span>"+item.seemore+"</span></a>";
        //                                    html+= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        //                                    html+= "<span class=\'course_catalog_container\' rel=\'"+item.codeitem+"\'><a class=\'addToCartCourse addToCartCourseClick\' style=\'margin-top: -15px; background:-moz-linear-gradient(center top , #4F9D00, #3e7900);background: -webkit-gradient(linear, left top, left bottom, from(#4F9D00), to(#3e7900));background-image:-o-linear-gradient(top,  #4F9D00, #3e7900);\' href=\'#\'><span>"+item.addtocart+"</span></a></span>";
        //                                }
        //                                html+= "    </td></tr></table></div></td></tr></table></td></tr></table>";
        //                            $("#ElistCatalog").append(html);
        //               });
        //        });
        //        $("#ElistCatalog").html("testing");
                                        }

    function listAll()
    {    
        window.location.href = "index.php?module=ecommerce&name_category=All";
        
        //$(".ecommerce_text_category").removeClass("active");        
        //$("#cat-0").addClass("active");
                        
        //$(document).ready(function(){
        //       $("#divPaginator").empty();
        //       $("#divPaginator").removeAttr("style");
        //       $("#divPaginator").removeAttr("class");
        //       $("#ElistCatalog").empty();
        //        $("#divPaginator").paginate({
        //                        count 		: ' . ceil(count(EcommerceCatalog::create()->getCourseEcommerce()) / 10) . ',
        //                        start 		: 1,
        //                        display                 : 10,
        //                        border			: false,
        //                        text_color  		: "#888",
        //                        background_color    	: "#EEE",	
        //                        text_hover_color  	: "black",
        //                        background_hover_color	: "#CFCFCF",
        //                        onChange                : function(page){
        //                                                            $.ajax({
        //                                                                type: "POST",
        //                                                                //url: "main/index.php?module=ecommerce&cmd=Sessionlist&func=getCoursePaginator&page="+page+"&count=10&id_category=",
        //                                                                url: "main/index.php?module=ecommerce&cmd=Sessionlist&func=getCoursePaginator&page="+page+"&count=10",
        //                                                                //data: datos,
        //                                                                dataType: "json",
        //                                                                success: function(data){
        //                                                                $("#ElistCatalog").empty();
        //                                                                $.each(data, function(i, item) {
        //                            var html = "<table style=\'margin-bottom:20px;\' class=\'back_table_catalog\'><tr>";
        //                            var classbox = "";
        //                                if(item.image != "../../../main/img/pixel.gif"){
        //                                    var mt = "";
        //                                    if(item.h == "100px"){
        //                                        mt = "margin-top:30px"
        //                                    }else{
        //                                        mt = "margin-top:6px";
        //                                    }
        //                                    html+= "<td class=\'box_catalog\' style=\'text-align:center;padding:0px; width:200px;height:150px;\' ><img style=\'"+mt+";height: "+item.h+";width: "+item.w+";\' src=\'"+item.image+" \' /></td>";
        //                                    classbox = "style=\'height:130px; width:66.5%!important; margin-left:10px!important;\'";
        //                                }
        //                                if(item.description == false) { item.description = ""; }
        //                                html+= "<td class=\'box_catalog2\' "+classbox+"><table width=\'100%\'><tr style=\'height:70px;\'><td style=\'vertical-align:top; padding-left:10px;width:400px;\'><strong style=\'font-zise:12px;\'>"+item.title+"</strong><br />"+item.description+"</td></tr><tr style=\'height:30px\'><td style=\'vertical-align:top; padding-left:10px;width:400px;color:#000;\'><strong>"+item.langprice+": </strong>"+item.priceinfo+" &nbsp;&nbsp;&nbsp;<strong>'.get_lang('Access').': </strong>"+item.durationinfo+"</td></tr><tr  style=\'height:20px;\'><td style=\'text-align:right;\'><div style=\'float:right;\'><table rel=\'"+item.type+"\' id=\'shoppingCartCatalog\'><tr><td><a href=\'#\' class=\'addToCartCourse\' id=\'"+item.id+"\' onClick=\'return viewDetail(this);\'><span>"+item.seemore+"</span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=\'course_catalog_container\' rel=\'"+item.codeitem+"\'><a class=\'addToCartCourse addToCartCourseClick\' href=\'#\'><span>"+item.addtocart+"</span></a></span></td></tr></table></div></td></tr></table></td></tr></table>";
        //                            $("#ElistCatalog").append(html);
        //                                                                });
        //                                                                },
        //                                                                timeout:80000
        //                                                            });
        //                                                }
        //                });
                        
        //                    procesa="main/index.php?module=ecommerce&cmd=Sessionlist&func=getCoursePaginator&page=1&count=10";
        //                    $.getJSON(procesa,
        //                    function(json){
        //                            $.each(json, function(i, item) {
        //                            var html = "<table style=\'margin-bottom:20px;\' class=\'back_table_catalog\'><tr>";
        //                            var classbox = "";
        //                            if(item.description == false) { item.description = ""; }
        //                            if(item.image != "../../../main/img/pixel.gif"){
        //                            var mt = "";
        //                            if(item.h == "100px"){
        //                                mt = "margin-top:30px"
        //                            }else{
        //                                mt = "margin-top:6px";
        //                            }
        //                                html+= "<td class=\'box_catalog\' style=\'text-align:center;padding:0px; width:200px;height:150px;\' >";
        //                                html+= "<a href=\'javascript:void(0);\' id=\'"+item.id+"\' onClick=\'return viewDetail(this);\'>";
        //                                html+= "<img style=\'"+mt+";height: "+item.h+";width: "+item.w+";\' src=\'"+item.image+" \' />";
        //                                html+= "</a>";
        //                                html+= "</td>";
        //                                classbox = "style=\'height:130px; width:66.5%!important; margin-left:10px!important;\'";
        //                            }
                                        
        //                            if(item.chr_type_cost==0){
        //                                html+= "<td class=\'box_catalog2\' "+classbox+"><table width=\'100%\'><tr style=\'height:70px;\'><td style=\'vertical-align:top; padding-left:10px;width:400px;\'><strong style=\'font-zise:12px;\'>"+item.title+"</strong><br />"+item.summary+"</td></tr><tr style=\'height:30px\'><td style=\'vertical-align:top; padding-left:10px;width:400px;color:#000;\'><strong>"+item.langprice+": </strong>'.get_lang('Free').' &nbsp;&nbsp;&nbsp;<strong>'.get_lang('Access').': </strong>"+item.durationinfo+"</td></tr><tr  style=\'height:20px;\'><td style=\'text-align:right;\'><div style=\'float:right;\'><table rel=\'"+item.type+"\' id=\'shoppingCartCatalog\'><tr><td>";
        //                                html+= "<a href=\'#\' style=\'text-decoration: underline;\' id=\'"+item.id+"\' onClick=\'return viewDetail(this);\'><span>"+item.seemore+"</span></a>";
        //                                html+= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        //                                html+= "<span class=\'course_catalog_container\' rel=\'"+item.codeitem+"\'><a class=\'addToCartCourseFree\' style=\'margin-top: -15px;padding: 15px;\' href=\'#\'><span>"+item.addtocartfree+"</span></a></span>";
        //                            } else {
        //                                html+= "<td class=\'box_catalog2\' "+classbox+"><table width=\'100%\'><tr style=\'height:70px;\'><td style=\'vertical-align:top; padding-left:10px;width:400px;\'><strong style=\'font-zise:12px;\'>"+item.title+"</strong><br />"+item.summary+"</td></tr><tr style=\'height:30px\'><td style=\'vertical-align:top; padding-left:10px;width:400px;color:#000;\'><strong>"+item.langprice+": </strong>"+item.priceinfo+" &nbsp;&nbsp;&nbsp;<strong>'.get_lang('Access').': </strong>"+item.durationinfo+"</td></tr><tr  style=\'height:20px;\'><td style=\'text-align:right;\'><div style=\'float:right;\'><table rel=\'"+item.type+"\' id=\'shoppingCartCatalog\'><tr><td>";
        //                                html+= "<a href=\'#\' style=\'text-decoration: underline;\' id=\'"+item.id+"\' onClick=\'return viewDetail(this);\'><span>"+item.seemore+"</span></a>";
        //                                html+= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        //                                html+= "<span class=\'course_catalog_container\' rel=\'"+item.codeitem+"\'><a class=\'addToCartCourse addToCartCourseClick\' style=\'margin-top: -15px;padding: 15px; background:-moz-linear-gradient(center top , #4F9D00, #3e7900);background: -webkit-gradient(linear, left top, left bottom, from(#4F9D00), to(#3e7900));background-image:-o-linear-gradient(top,  #4F9D00, #3e7900);\' href=\'#\'><span>"+item.addtocart+"</span></a></span>";
        //                            }
        //                            html+= "    </td></tr></table></div></td></tr></table></td></tr></table>";
        //                            $("#ElistCatalog").append(html);
        //                        });
        //                    });
        //                    $("#ElistCatalog").html("testing");
        //            });
                                    }

    function viewDetail(element)
    {
        $(document).ready(function(){
        $("#divDialog").empty();
            $("#divDialog").dialog("open");
            $(".ui-dialog-titlebar-close").show();
            procesa="main/index.php?module=ecommerce&cmd=Sessionlist&func=getItemDetail&id="+element.id;
            $.getJSON(procesa,
            function(json){
                $("#divDialog").html("<p style=\"height: 330px;    overflow: auto;\">"+json.description+""+json.button+"</p>");
                $("#divDialog" ).dialog({
                        autoOpen: true,
                        title: json.title,
                        modal: true,
                        show: "blind",
                        closeText: "'.get_lang("Close").'",
                        width: 600,
                        height: 450,
                        hide: "explode"
                    });                            
                }); 
            return false;
            });
        return false;alert($("#button_start").attr("id"));
    }
    
$(function() {
        $("#divPaginator12").paginate({
                count 		: ' . ceil(count(EcommerceCatalog::create()->getCourseEcommerce('', $_GET['id_category'])) / 10) . ',
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
                        url: "main/index.php?module=ecommerce&cmd=Sessionlist&func=getCoursePaginator&page="+page+"&count=10&id_category='.intval($_GET['id_category']).'&type_category=index",
                        dataType: "json",
                        success: function(data){
                            $("#ElistCatalog").empty();
                            $.each(data, function(i, item) {
                                if(item.description == false) {item.description = ""; };
                                if(item.summary == false) {item.summary = ""; };
                                var html = "<table style=\'margin-bottom:20px;\' class=\'back_table_catalog\'><tr>";
                                var classbox = "";
                                if(item.image != "../../../main/img/pixel.gif") {
                                var mt = "";
                                if(item.h == "100px"){
                                    mt = "margin-top:30px"
                                }else{
                                    mt = "margin-top:6px";
                                }
                                        html+= "<td class=\'box_catalog\' style=\'text-align:center;padding:0px; width:200px;height:150px;\' >";
                                        html+= "<a href=\'javascript:void(0);\' id=\'"+item.id+"\' onClick=\'return viewDetail(this);\'>";
                                        html+= "<img style=\'"+mt+";height: "+item.h+";width: "+item.w+";\' src=\'"+item.image+" \' />";
                                        html+= "</a>";
                                        html+= "</td>";
                                            classbox = "style=\'height:130px; width:66.5%!important; margin-left:10px!important;\'";
                                }
                                            html+= "<td class=\'box_catalog2\' "+classbox+"><table width=\'100%\'><tr style=\'height:70px;\'><td style=\'vertical-align:top; padding-left:10px;width:400px;\'><strong style=\'font-zise:12px;\'>"+item.title+"</strong><br />"+item.summary+"</td></tr><tr style=\'min-height:30px\'><td style=\'vertical-align:top; padding-left:10px;width:400px;color:#000;\'><strong>"+item.langprice+": </strong>"+item.priceinfo+" &nbsp;&nbsp;&nbsp;<strong>' . get_lang('CourseAccess') . ': </strong>"+item.durationinfo+"</td></tr><tr  style=\'height:20px;\'><td style=\'text-align:right;\'><div style=\'float:right;\'><table rel=\'"+item.type+"\' id=\'shoppingCartCatalog\'><tr><td>";
                                                if(item.chr_type_cost==0){
                                                    html+= "<a href=\'#\' style=\'text-decoration: underline;\' id=\'"+item.id+"\' onClick=\'return viewDetail(this,\""+item.image+"\");\'><span>"+item.seemore+"</span></a>";
                                                    html+= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                                                    html+= "<span class=\'course_catalog_container\' rel=\'"+item.codeitem+"\'><a class=\'addToCartCourseFree\' href=\'#\'><span>"+item.addtocartfree+"</span></a></span>";
                                                }else{
                                                    html+= "<a href=\'#\' style=\'text-decoration: underline;\' id=\'"+item.id+"\' onClick=\'return viewDetail(this,\""+item.image+"\");\'><span>"+item.seemore+"</span></a>";
                                                    html+= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                                                    html+= "<span class=\'course_catalog_container\' rel=\'"+item.codeitem+"\'><a class=\'addToCartCourse addToCartCourseClick\'  href=\'#\'><span>"+item.addtocart+"</span></a></span>";
                                    }
//                            html+= "class=\'addToCartCourse addToCartCourseClick\' href=\'#\'><span>"+item.addtocart+"</span></a></span>";
                            html+= "</td></tr></table></div></td></tr></table></td></tr></table>";
                            $("#ElistCatalog").append(html);
                            /*
                            TOOLTIP CUT
                            */
                            if ($(".cut-tooltip").length > 0) {
                              $(".cut-tooltip[title]").qtip({
                                              position: {
                                                      viewport: $(window)
                                                      },
                                  style: { 
                                      width: 800,
                                      padding: 5,
                                      background: "#A2D959",
                                      color: "black",
                                      textAlign: "center",
                                      border: {
                                         width: 7,
                                         radius: 5,
                                         color: "#A2D959"
                                      },
                                      name: "dark"
                                   }
                              });
                            }
                        /*
                        FIN TOOLTIP CUT
                        */
                    });
                },
                timeout:80000
           });
        }
    });
    
  
});

</script>';

echo '<script type="text/javascript">
            $(function() {
		$("#divPaginator").paginate({
                count                  : ' . ceil(count(EcommerceCatalog::create()->getCourseEcommerce()) / 8) . ',
                start                  : 1,
                display                : 8,
                border                 : false,
                text_color             : "#888",
                background_color       : "#EEE",
                text_hover_color       : "black",
                background_hover_color : "#CFCFCF",
                onChange               : function(page) {
                                            $(".cursus__content").html("<li class=\"preload1\"><img src=\"'. api_get_path(WEB_IMG_PATH).'navigation/ajax-loader.gif\" /></li>");
                                            $.ajax({
                                                type: "POST",
                                                url: "/main/index.php?module=ecommerce&cmd=Sessionlist&func=getCoursePaginatorHtml&page="+page+"&count=8&value=true&by_sort="+ $("#by_sort").val(),
                                                dataType: "html",
                                                success: function(data) {
                                                    $(".cursus__content").html(data);
                                                }                          
                                            });
                                          }, 
                timeout: 10000
            });

                $("#accordion").accordion({
                    heightStyle: "content",
                    collapsible: true

                });
            }); 
</script>';

    $category = '';
    if (isset($_GET['id_category'])){
        $category = intval($_GET['id_category']);
    }
    
    $checkTab = api_get_show_catalogo_tab();
    $mutiLanguage = (api_get_setting("multi_language") === 'true') ? true : false;
    
    if(!$checkTab OR !$mutiLanguage){
        $lista = EcommerceCatalog::create()->getHtmlCatalog($category); //getProductList; 
        $display_catalog = api_get_setting('display_catalog_on_homepage');
        if (($display_catalog == 'true')&&($_GET['action']!='show')) {
            echo $lista;
        }
    }
    
    //Dialog Box Add Shopping Cart
    /*echo '<div class="txtclose" style="display:none;">'.get_lang("Close").'</div>';
    echo '<div style="display:none;" id="shoppingMsgBody" title="' . get_lang('ShoppingCart') . '">';
    echo '<center><img src="main/img/shopcart_general.png" style="vertical-align:text-bottom;margin-bottom: 20px; margin-top: 35px;"><br/>' . get_lang('AcceptAddItemShoppingCart') . '</center>';
    echo '</div>';
    echo '<div style="display:none;" id="shoppingMsgBodyAdded">';
    echo '<center><img src="main/img/shopcart_general.png" style="vertical-align:text-bottom;margin-bottom: 20px; margin-top: 35px;"><br/>' . get_lang('ItemAddToShoppingCart') . '</center>';
    echo '</div>';*/
//}
//echo'<div id="divYes" style="display:none;">' . get_lang('langYes') . '</div>';
//echo'<div id="divNo"  style="display:none;">' . get_lang('langNo') . '</div>';

if (intval($_GET['popup']) > 0) {
?>
<div class="row">
            <section class="catalog__content catContent wrap">
            </section>
</div>
<?php
// display the footer
Display :: display_footer();
exit;
}

$show_menu = false;
if ((api_is_allowed_to_create_course() || api_is_session_admin()) && ($_SESSION["studentview"] != "studentenview")) {
    $show_menu = true;
}
if ($show_menu) {

    //echo "<div class=\"left__content sidebar sidebar--cursus\">";
    display_anonymous_menu();
    if (api_get_setting('show_opened_courses') == 'true') {
        display_open_course();
    }
    if(api_is_platform_admin() && api_is_sas_version()){
	/*echo ' <div id="update-sas_portal" style="width:100%">
			<span id="trial-count-day">'.(api_get_setting("saas_current_version") == 'pro' ?get_lang('YourPortalWillExpireIn'):get_lang('YourTrialWillExpireIn')) . ' <span id="trial-nb-day"></span> ' . strtolower(get_lang('Days')).'</span><br />
			<button type="button" class="upgrade_link" id="upgrade-trial">'.get_lang('UpgradeTrial').'</button>
		</div>';*/
	}
    echo '<div class="clear"></div>';
    echo '</div>';
} else {

    if (!($_user['user_id']) || api_is_anonymous($_user['user_id'])) {

        echo '<div class="menu" id="menu">';
        echo '<div class="back-top">';
        echo '</div>';
        display_anonymous_menu();
        if (api_get_setting('show_opened_courses') == 'true') {
            display_open_course();
        }
        echo '<div class="back-bottom">';
        echo '</div>';
        echo '</div>';
        
    } else {

        /*$show_opened_courses = api_get_setting('show_opened_courses');
        $display_open_course = display_open_course($);*/
        

       
            echo '<div class="menu" id="menu">';
            echo '<div class="back-top">';
            echo '</div>';
            display_anonymous_menu();
            if (api_get_setting('show_opened_courses') == 'true') {
                display_open_course();
            }
            echo '<div class="back-bottom">';
            echo '</div>';
            echo '</div>';
         
    }
	
}

function check_left_empty() {
    if (!($_user['user_id']) || api_is_anonymous($_user['user_id'])) {
        $login = true;
    } else {
        $login = false;
    }
    $show_opened_courses = api_get_setting('show_opened_courses');
    $display_open_course = display_open_course(true);
    $display_open_course = ($display_open_course == 0) ? true : false;

    if ($display_open_course AND $login AND $show_opened_courses) {
        return true;
    } else {
        return false;
    }
}

function display_open_course($count = false) {
    $tbl_course = Database :: get_main_table(TABLE_MAIN_COURSE);
    $sql = "SELECT * FROM $tbl_course WHERE visibility = 3";
    if (isset($_SESSION['_user']['user_id']) && $_SESSION['_user']['user_id'] != 0) {
        $sql = "SELECT * FROM $tbl_course WHERE visibility = 3 or visibility=2";
    }
    $res = Database::query($sql, __FILE__, __LINE__);
    $num_rows = Database::num_rows($res);
    if ($count) {
        return $num_rows;
    }
    if ($num_rows <> 0) {
        echo '<div class="menu" id="menu">';
        echo "<div class=\"section1\">";
        echo '<div class="row"><h2 style="padding-left:10px">' . get_lang('OpenCourses') . '</h2></div>';
        echo "	<div class=\"sectioncontent\">";
        while ($row = Database::fetch_array($res)) {
            $title = $row['title'];
            $directory = $row['directory'];
            echo '<a href="' . api_get_path(WEB_COURSE_PATH) . $directory . '/?id_session=0">
                <div class="OpenCoursesHomeContent"><div class="OpenCoursesHomeImage">
                <img alt="' . $title . '" title="' . $title . '" src="main/img/catalogue_22.png" style="vertical-align:text-bottom;" /></div>
                    <div class="OpenCoursesHomeDescription">' . $title . '</div>
                        <div class="clear"></div>
                </div></a>';
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
            if ((api_get_user_id() > 0 && $course['visibility'] == COURSE_VISIBILITY_OPEN_PLATFORM) || ($course['visibility'] == COURSE_VISIBILITY_OPEN_WORLD)) {
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
    //echo "<li><a href=\"main/create_course/add_course.php\">" . get_lang("CourseCreate") . "</a></li>";
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

function display_logo_dokeos() {
//    $html .= '<div class="dokeos-logo">';
//    $html .= '<a href="http://www.dokeos.com" target="_blank" >';
//    $html .= '<img alt="www.dokeos.com" title="www.dokeos.com"  src="' . api_get_path(WEB_IMG_PATH) . 'logo-dokeos-icon-4.png" />' . get_lang('dokeos');
//    $html .= '</a>';
//    $html .= '</div>';
//    return $html;
}

function display_like_on_socialred(){
    
    $user_selected_language = api_get_interface_language();
    if (!isset($user_selected_language)) {
        $user_selected_language = api_get_setting('platformLanguage');
    }
    
    switch($user_selected_language){
        case 'french':
            $dokeosclub = "http://www.facebook.com/dokeosclub.fr";
            $language = "fr_FR";
        break;
        case 'spanish':
            $dokeosclub = "http://www.facebook.com/dokeoslatino";
            $language = "es_LA";
        break;
        default:
            $dokeosclub = "http://www.facebook.com/dokeosclub.en";
            $language = "en_EN";
        break;
    }

    $like .= '<div id="fb-root"></div> 
    <script>(function(d, s, id) { 
    var js, fjs = d.getElementsByTagName(s)[0]; 
    if (d.getElementById(id)) return; 
    js = d.createElement(s); js.id = id; 
    js.src = "//connect.facebook.net/'.$language.'/all.js#xfbml=1"; 
    fjs.parentNode.insertBefore(js, fjs); 
    }(document, "script", "facebook-jssdk"));</script>';

    $like .= '<div class="fb-like" data-href="'.$dokeosclub.'" data-send="false" data-layout="box_count" data-width="450" data-show-faces="false"></div>';
       
    $html = (!empty($user_selected_language)) ? '<div style="float: right;width: auto;" >'.$like.'</div>' : '';
    
    $likeOnSocialRed = api_get_setting('show_like_on_facebook');
    
    if($likeOnSocialRed == 'true'){
        return $html;
    }
    
}
/**
 * Displays the menu for anonymous users:
 * login form, useful links, help section
 * Warning: function defines globals
 * @version 1.0.1
 * @todo does $_plugins need to be global?
 */
if (api_get_setting('display_catalog_on_homepage') == 'true' && api_get_setting('enable_shop_tool') == 'true') {
    echo '<input type="hidden" name="varLangStart" id="varLangStart" value="' . get_lang('FirstPage') . '" />';
    echo '<input type="hidden" name="varLangEnd" id="varLangEnd" value="' . get_lang('LastPage') . '" />';
}

function display_anonymous_menu() {
    global $loginFailed, $_plugins, $_user, $menu_navigation, $css_type;
    global $home, $home_old;
    
    // language
    $platformLanguage       = api_get_setting('platformLanguage');
    $user_selected_language = api_get_interface_language();   
    if (!isset($user_selected_language)) {
        $user_selected_language = $platformLanguage;
    } 
    $language_id = api_get_language_id($user_selected_language);
    
    // access url id
    $accessUrlId = api_get_current_access_url_id();            
    if ($accessUrlId< 0) {
        $accessUrlId = 1;
    }
    
    if (!($_user['user_id']) || api_is_anonymous($_user['user_id'])) { // only display if the user isn't logged in
        display_login_form($loginFailed);

        /*if ($loginFailed) {
            handle_login_failed();
        }*/

        if (api_number_of_plugins('loginpage_menu') > 0) {
            echo '<div class="note" style="background: none">';
            api_plugin('loginpage_menu');
            echo '</div>';
        }
    } else {
		display_catalogue_info();
	}
    $checkTab = api_get_show_catalogo_tab();
    $mutiLanguage = (api_get_setting("multi_language") === 'true') ? true : false;
    

        if (api_get_setting('display_categories_section_on_homepage') == 'true' AND api_get_setting('display_catalog_on_homepage')=='true') {
            /*echo '<div class="section">';
            display_anonymous_course_list();
            echo '</div>';*/
        }

	// language
    $platformLanguage       = api_get_setting('platformLanguage');
    $user_selected_language = api_get_interface_language();   
    if (!isset($user_selected_language)) {
        $user_selected_language = $platformLanguage;
    } 
    $language_id = api_get_language_id($user_selected_language);

	$accessUrlId = api_get_current_access_url_id();            
	if ($accessUrlId < 0) {
		$accessUrlId = 1;
	}
	$tblNode =Database :: get_main_table(TABLE_MAIN_NODE);
	if(api_is_anonymous()){
		$chkenabled = " AND n.enabled = 1";
	}
	else {
		$chkenabled = '';
	}
		$sqlProm = "SELECT n.content, n.title, n.display_title 
					FROM  $tblNode n										
					WHERE n.language_id IN (". $language_id.",0)
						  AND n.access_url_id =".$accessUrlId."
						  AND n.active = 1".$chkenabled;

		$results = Database::query($sqlProm, __FILE__, __LINE__);		
	?>
	<section class="main__content content">
	<?php
	
	if(api_get_user_id() <> 0){
	display_slide();
	}

	while ($row = Database::fetch_array($results))
        {
			$title = $row['title'];
			$content = $row['content'];
	
	if(api_get_setting('display_catalog_on_homepage') == 'false'){
	
	?>
    <!--<h1 class="title--page"><?php echo $title; ?></h1>-->
    <div class="content__intro">	
		<?php
		if(api_get_user_id() <> 0){
		?>
		<p>
			<?php echo str_replace('{WEB_PATH}', api_get_path(WEB_PATH), $content); ?>
		</p>
		<?php
		}
		?>
	</div>
	<?php
		}
		}	

	$objCatalog = new EcommerceCatalog();
	$objCatalog->getCatalogSettings();
	if(api_get_setting('display_catalog_on_homepage') == 'true'){
	echo '<span id="CatalogueProducts">';
	echo $objCatalog->getCatalogueProductsHtml();
	echo '</span>';
	}
    
    if (isset($_SESSION['_user']['user_id']) && $_SESSION['_user']['user_id'] != 0) {
        $show_menu = false;
        $show_course_link = false;

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
        
        /*if ($show_menu) {
            echo "<div class=\"section1 \">";            
            if ($css_type == 'tablet') {
                echo api_display_tool_title(get_lang('MenuUser'), 'tablet_title');
                if ((api_get_setting('allow_users_to_create_courses') == 'true') || api_is_platform_admin()) {
                    display_create_course_link_tablet();
                }
                display_trainer_links_tablet();
                if ($show_digest_link) {
                    display_digest($toolsList, $digest, $orderKey, $courses);
                }
            } else {
                echo "	<div class='sectiontitle'>" . get_lang("MenuUser") . "</div>";
                echo "	<div class='sectioncontent'>";
                echo "		<ul class='menulist nobullets'>";
                if ((api_get_setting('allow_users_to_create_courses') == 'true') || api_is_platform_admin()) {
                    display_create_course_link();
                }
                display_trainer_links();
                echo "		</ul>";
                echo "	</div>";
            }
            echo '<div class="clear"></div>';
            echo '</div>';
        }*/

        /*if (!empty($menu_navigation)) {
            echo "<div class='section'>";
            echo "<div class='sectiontitle'>" . get_lang("MainNavigation") . "</div>";
            echo '<div class="sectioncontent">';
            echo "<ul class='menulist nobullets'>";
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
        }*/
    }

    
    
    $menulinks = get_menu_links(MENULINK_CATEGORY_LEFTSIDE);
    
    if (count($menulinks) > 0){

        if ($css_type == 'tablet') {
            echo '<div class="menu-general nobullets">';
            echo api_display_tool_title(get_lang('MenuGeneral'), 'tablet_title');
            //$menu_content = str_replace(array('<li>', '</li>'), array('<div>', '</div>'), $menu_content);
            foreach($menulinks as $row){
                echo '<div><a href='.$row['link_path'].' target='.$row['target'].'>'.$row['title'].'</a></div>';
            }
            //echo $menu_content;
            echo '</div>';
        } else {
            echo "<div class='section1 menu-more'>", "<div class='sectiontitle'>" . get_lang("MenuGeneral") . "</div>";
            echo '<div class="sectioncontent nobullets">';
            //echo $menu_content;
            echo '</div>';
            echo '</div>';
        }
    }

    /*$checkTab = api_get_show_catalogo_tab();
    $mutiLanguage = (api_get_setting("multi_language") === 'true') ? true : false;
    
    if($checkTab OR $mutiLanguage){ 
		echo 'anjan1111';
        if (api_get_setting('display_catalog_on_homepage') == 'true' && api_get_setting('enable_shop_tool') == 'true' ) {
			echo '2222';
            if(count(EcommerceCatalog::create()->getCourseEcommerce()) > 0){
				$objCatalog = new EcommerceCatalog();
				$objCatalog->getCatalogSettings();
				$lista = EcommerceCatalog::create()->getHtmlCatalog(true);
                echo '<div class="cursus__content">';
                echo $lista;        
                echo '</div>';
            }
        }
    }*/
    
	echo '</section></div>';

    if ($_user['user_id'] && api_number_of_plugins('campushomepage_menu') > 0) {
        echo '<div class="note" style="background: none">';
        api_plugin('campushomepage_menu');
        echo '</div>';
    }
    
    $language_id = api_get_language_id(api_get_language_interface($_user));
    $tblNode = Database::get_main_table(TABLE_MAIN_NODE);
                                $sql="SELECT id,title,content,active FROM $tblNode 
                                        WHERE active = ".ACTIVE." 
                                        AND node_type =".NODE_NOTICE." 
                                        AND access_url_id =".$accessUrlId." 
                                        AND language_id IN (". $language_id.",0)";
                                        
                                
                                $result = Database::query($sql,__FILE__,__LINE__);
                                $row = Database::fetch_array($result);
                                
   
                            if ($row['active']!=0) {
        
                                    echo '<div class="section1">';
                                    echo '<h2 style="padding-left:10px">'.$row['title'].'</h2>';
                                    echo '<div class="" style="overflow-x:auto; margin-right:2px !important;">';
                                    echo '<table><tr>';
                                            echo '</tr><td style="padding-top:2px">'.$row['content'].'</td></tr>';
                                    echo '</table>';
                                    echo '</div>';
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
    echo "<div id='login_fail'>" . $message . "</div>";
    echo '<script type="text/javascript">
            $(function() {
                // Handler for .ready() called.
                $("input[name=\"password\"]").val("");
            });
        </script>';
}

function display_slide() {

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

	$slides_table = Database :: get_main_table(TABLE_MAIN_SLIDES);
	$sql = "SELECT * FROM $slides_table WHERE language = '" . Database::escape_string(Security::remove_XSS($language)) . "' ORDER BY display_order";
	$res = Database::query($sql, __FILE__, __LINE__);
	$num_of_slides = Database::num_rows($res);
	$slides = array();
	$img_dir = api_get_path(WEB_PATH) . 'home/default_platform_document/';

	if ($num_of_slides <> 0) {
		echo '<section class="slider"><div class="flexslider"><ul class="slides">';
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

			echo '<li>
			<a href="'.$link.'"><img src="'.$image.'" /></a>
		  <p class="flex-caption">'.$title.'</p>
			</li>';
		}
		echo '</ul></div></section>';
	}
}

/**
 * 	Adds a form to let users login
 * 	@version 1.1
 */
function display_login_form1() {
    $form = new FormValidator('formLogin');
    $form->addElement('text', 'login', get_lang('UserName'));
    $form->addElement('password', 'password', get_lang('Pass'));
    $form->addElement('style_submit_button', 'submitAuth', get_lang('langEnter'),'onclick="validateSaas()" class = "login login-home"');    
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
    $html_ul .= display_logo_dokeos();
    $html_ul .=display_like_on_socialred();
        
    // Add html element, Button
    $form->addElement('html', $html_ul);

    api_display_language_form(true, 'none;padding-left:5px', false, '12px');
    $form->display();

    if (api_get_setting('openid_authentication') == 'true') {
        include_once 'main/auth/openid/login.php';
        echo '<div class="section1">
                        <div class="sectiontitle">' . get_lang('OpenIdAuthentication') . '</div>
                        <div class="sectioncontent nobullets">';
        echo openid_form();
        echo '<br/><br/>
                        </div>';

        echo '</div>';
    }
    //Enter with cas for Paris5
    if (api_get_setting('cas_activate') == 'true') {

        echo '<div class="section1" style="padding:8px;">
                        <div class="sectioncontent nobullets">';
        echo '<form action="main/auth/cas/logincas.php" method="post" id="loginform" name="formLogin">
                            <input type="hidden" name="isportalcas"  value="" />
                            <button type="submit" name="submitAuth" class="login login-home">' . get_lang('CasLogin') . '</button>
                        </form>
                        <br/><br/>
                        </div>';

        echo '</div>';
    }
}

function display_login_form2() {
	global $language_interface;
	?>
        <div class="row">
            <aside class="left__content sidebar sidebar--catalog">
     			 <h1 class="login--page">Connexion</h1>

                <div class="elemlanguages">
                    <label for="langues">Langues </label>
                    <?php
					api_display_language_form(true, 'none;padding-left:5px', false, '12px');
					?>
                </div>
                <div class="elemConnexion">
					<form id="formLogin" class="elemConnexion__form" name="formLogin" method="post" action="index.php?language=<?php echo $language_interface?>" class="loginform">
                        <p class="elemConnexion__item">
                            <label for="login"><?php echo get_lang('UserName'); ?></label>
                           <div class="input-wrap">
                        <i class="icon-user"></i>
                            <input type="text" name="login" id="login" placeholder="Entrez votre login" />
				</div>
                        </p>
                        <p class="elemConnexion__item">
                            <label for="password">Mot de passe :</label>
				<div class="input-wrap">
                        <i class="icon-lock"></i>
                            <input type="password" name="password" id="password" placeholder="Entrez votre mot de passe" />
				</div>
                        </p>
                        <p class="elemConnexion__item" style="float:none;text-align:center;margin-top:38px;">
                            <a href="#" class="btn" id="submit-auth">
	                                <span>Se connecter</span>
                            </a>
                        </p>
                
                    </form>
                </div>
				<div id="login-fail-message"></div>
            </aside>
           <p class="elemConnexion__item  txt-center">
                            <a href="main/auth/lostPassword.php" class="forgot">
                                <?php echo get_lang("LostPassword"); ?>
                            </a>
                        </p>

            <section class="main__content content mainConnexion">
                <div class="mainConnexion__right">
                    
                </div>
            </section>
        </div>
		<?php
}

function display_login_form($loginFailed) {
	global $language_interface;
	echo '<script type="text/javascript">
$(document).ready( function() {

$(".display_checkbox").change(function() {
	var filter_id = [];
		$(".display_checkbox").each(function(){
			if($(this).prop("checked")) {				
				var attrid = $(this).attr("id");
				filter_id.push($(this).attr("id"));
			}
	});
if(filter_id != ""){

	$.ajax({
            url:"'. api_get_path(WEB_AJAX_PATH).'categories.ajax.php?action=showfilter&id="+filter_id,
            type:"POST",
            success: function(data) {

                $("#CatalogueProducts").html(data);
                //cut_tooltip();                    
            },
            beforeSend : function (){                              
                $("#CatalogueProducts").html("<img src=\"'. api_get_path(WEB_IMG_PATH).'navigation/ajax-loader.gif\" />");
            }
        })
} else {
	window.location.href = "'.api_get_path(WEB_PATH).'index.php";
}
});

$(".btn--icon").live("click",function(e) {
    e.preventDefault();
    var getid = $(this).attr("id");
      //var getid = $(".banner__select option:selected").attr("id");
    var numpayment = 1;
    $.ajax({
            url:"'. api_get_path(WEB_AJAX_PATH).'categories.ajax.php?action=showsub&id="+escape(getid),
            type:"POST",
            success: function(data) {                
		$(".block__listitem").removeClass("current");
                $("#li_"+getid).addClass("current");
                $("#CatalogueProducts").html(data);
                //cut_tooltip();                    
            },
            beforeSend : function (){                              
                $("#CatalogueProducts").html("<img src=\"'. api_get_path(WEB_IMG_PATH).'navigation/ajax-loader.gif\" />");
            }
        })
});

});

</script>';
	?>
		<!--<div id="languages">            
            <?php if ($language_interface == 'french'): ?>
                <a href="index.php?language=english"><img src="<?php echo api_get_path(WEB_IMG_PATH).'en.png'; ?>">&nbsp;<?php echo get_lang('English'); ?></a>&nbsp;&nbsp;
                <img src="<?php echo api_get_path(WEB_IMG_PATH).'fr.png'; ?>">&nbsp;<?php echo get_lang('French'); ?>
            <?php else: ?>
                <img src="<?php echo api_get_path(WEB_IMG_PATH).'en.png'; ?>">&nbsp;<?php echo get_lang('English'); ?>&nbsp;&nbsp;
                <a href="index.php?language=french"><img src="<?php echo api_get_path(WEB_IMG_PATH).'fr.png'; ?>">&nbsp;<?php echo get_lang('French'); ?></a>
            <?php endif; ?>                        
        </div>--> 
        <div class="row">
            <aside class="left__content sidebar sidebar--catalog">
     			 <div class="sidebar__block block block--login block__content">
				 <?php
                        api_display_language_form(true, '', false, '12px');
                 ?>
                    <form id="formLogin" class="login__form" name="formLogin" method="post" action="index.php?language=<?php echo $language_interface?>" class="loginform">
                        <p class="elemConnexion__item">
                            <label for="login"><?php echo get_lang('Login'); ?></label>
                            <input type="text" name="login" id="login" />
                        </p>
                        <p class="elemConnexion__item">
                            <label for="password"><?php echo get_lang('Password'); ?></label>
                            <input type="password" name="password" id="password" />
                        </p>
                        <p class="lost">
                            <a href="main/auth/lostPassword.php"><?php echo get_lang("LostPassword"); ?></a>
                        </p>

                        <p class="submit">
                            <input type="submit" class="btn"  value="<?php echo get_lang('SIdentifier'); ?>" />
                        </p>
                                            </form>
                </div>
				<!--<div id="login_fail"></div>-->
			<?php
			if($loginFailed){
                        handle_login_failed();
                     }
			if(api_get_setting('display_catalog_on_homepage')=='true' && api_get_setting('display_catalogue_international') == 'true') {
			$list_course_extra_field = CourseManager::get_addcourse_extra_field_list();
			$filter_count = sizeof($list_course_extra_field);
			if($filter_count > 0){
			?>
				<div class="sidebar__block block block--checkbox">
                    <h2 class="block__title"><?php echo get_lang("NosFormations"); ?></h2>
<div class="block__content">
					<?php
					$list_course_extra_field = CourseManager::get_addcourse_extra_field_list();
					foreach($list_course_extra_field as $extra_field){						
					?>
                    <p>
                        <input type="checkbox" id="<?php echo $extra_field['id']; ?>" class="display_checkbox" /> <label for="<?php echo $extra_field['id']; ?>"><?php echo $extra_field['field_display_text']; ?></label>
                    </p>
					<?php
					}
					?>                    
                </div>
</div>
				<?php
				}
				$objCatalog = new EcommerceCatalog();
				$categories = $objCatalog->getListCategoryCourse(false);
				?>
                <div class="sidebar__block block block--list">
                    <h2 class="block__title"><?php echo get_lang("Categories"); ?></h1>
<div class="block__content">
                        <ul class="block__list">
						<li class="block__listitem current">
                            <a href="index.php" class="btn--icon1">
                                <span><?php echo get_lang("All"); ?></span>
                                <i class="icon-arrow-right right"></i>
                            </a>
                        </li>
						<?php 
						foreach($categories as $category): 
						?>
                        <li class="block__listitem" id="<?php echo 'li_'.$category['code']; ?>">
                            <a href="#" id="<?php echo $category['code']; ?>" class="btn--icon">
                                <span><?php echo $category['name']; ?></span>
                                <i class="icon-arrow-right right"></i>
                            </a>
                        </li>
						<?php endforeach; ?>                        
                    </ul>
                </div>
</div>
		<?php
		}
		?>
            </aside>
           
            <!--<section class="main__content content">
                <h1 class="title--page">Catalogue</h1>
                <div class="content__intro">
				</div>
            </section>-->
        
		<?php
}

function display_catalogue_info() {
	global $language_interface;
	echo '<script type="text/javascript">
	$(document).ready( function() {
	$(".display_checkbox").change(function() {
		var filter_id = [];
			$(".display_checkbox").each(function(){
				if($(this).prop("checked")) {					
					var attrid = $(this).attr("id");
					filter_id.push($(this).attr("id"));
				}
		});
	if(filter_id != ""){

		$.ajax({
				url:"'. api_get_path(WEB_AJAX_PATH).'categories.ajax.php?action=showfilter&id="+filter_id,
				type:"POST",
				success: function(data) {
					$("#CatalogueProducts").html(data);
					//cut_tooltip();                    
				},
				beforeSend : function (){                              
					$("#CatalogueProducts").html("<img src=\"'. api_get_path(WEB_IMG_PATH).'navigation/ajax-loader.gif\" />");
				}
			})
} else {
	window.location.href = "'.api_get_path(WEB_PATH).'index.php";
}
	});

	$(".btn--icon").live("click",function(e) {
    e.preventDefault();
    var getid = $(this).attr("id");
      //var getid = $(".banner__select option:selected").attr("id");
    var numpayment = 1;
    $.ajax({
            url:"'. api_get_path(WEB_AJAX_PATH).'categories.ajax.php?action=showsub&id="+escape(getid),
            type:"POST",
            success: function(data) {                
		$(".block__listitem").removeClass("current");
                $("#li_"+getid).addClass("current");
                $("#CatalogueProducts").html(data);
                cut_tooltip();                    
            },
            beforeSend : function (){                              
                $("#CatalogueProducts").html("<img src=\"'. api_get_path(WEB_IMG_PATH).'navigation/ajax-loader.gif\" />");
            }
        })
});

	});
	</script>';
	?>
        <div class="row">
            <aside class="left__content sidebar sidebar--catalog">     			 
		<?php 		
		if(api_get_setting('display_catalog_on_homepage')=='true' && api_get_setting('display_catalogue_international') == 'true') {
		$list_course_extra_field = CourseManager::get_addcourse_extra_field_list();
		$filter_count = sizeof($list_course_extra_field);
		if($filter_count > 0){
		?>
				<div class="sidebar__block block block--checkbox">
                    <h2 class="block__title"><?php echo get_lang("NosFormations"); ?></h2>
<div class="block__content">
					<?php
					//$list_course_extra_field = CourseManager::get_addcourse_extra_field_list();
					foreach($list_course_extra_field as $extra_field){						
					?>
                    <p>
                        <input type="checkbox" id="<?php echo $extra_field['id']; ?>" class="display_checkbox" /> <label for="<?php echo $extra_field['id']; ?>"><?php echo $extra_field['field_display_text']; ?></label>
                    </p>
					<?php
					}
					?>
</div>                    
                </div>
				<?php
				}
				$objCatalog = new EcommerceCatalog();
				$categories = $objCatalog->getListCategoryCourse(false);
				?>
                <div class="sidebar__block block block--list">
                    <h2 class="block__title"><?php echo get_lang("Categories"); ?></h1>
<div class="block__content">
                        <ul class="block__list">
						<li class="block__listitem current">
                            <a href="index.php" class="btn--icon1">
                                <span><?php echo get_lang("All"); ?></span>
                                <i class="icon-arrow-right right"></i>
                            </a>
                        </li>
			<?php 
						foreach($categories as $category): 
						?>
                        <li class="block__listitem" id="<?php echo 'li_'.$category['code']; ?>">
                            <a href="#" id="<?php echo $category['code']; ?>" class="btn--icon">
                                <span><?php echo $category['name']; ?></span>
                                <i class="icon-arrow-right right"></i>
                            </a>
                        </li>
						<?php endforeach; ?>                        
                    </ul>
</div>
                </div>
		<?php
		}
		?>
            </aside>
           
            <!--<section class="main__content content">
                <h1 class="title--page">Catalogue</h1>
                <div class="content__intro">
				</div>
            </section>-->
        
		<?php
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
        $htmlListCat = "<h3 class='tablet_title'>" . get_lang("CatList") . "</h3>";
        $htmlListCat .= "<ul style='list-style-type:none;margin-left:0px;'>";
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
                    $courses_list_string .= "<a href=\"" . $web_course_path . $course['directory'] . "/\">" . api_ucfirst($course['title']) . "</a><br />";
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
                if ($course['visibility'] == COURSE_VISIBILITY_OPEN_WORLD || ($user_identified && $course['visibility'] == COURSE_VISIBILITY_OPEN_PLATFORM) || ($user_identified && key_exists($course['code'], $courses_of_user) && $course['visibility'] != COURSE_VISIBILITY_CLOSED) || $courses_of_user[$course['code']]['status'] == '1' || api_is_platform_admin()) {
                    $courses_list_string .= "<b><a href=\"" . $web_course_path . $course['directory'] . "/\">";
                }
                $courses_list_string .= $course['title'];
                if ($course['visibility'] == COURSE_VISIBILITY_OPEN_WORLD || ($user_identified && $course['visibility'] == COURSE_VISIBILITY_OPEN_PLATFORM) || ($user_identified && key_exists($course['code'], $courses_of_user) && $course['visibility'] != COURSE_VISIBILITY_CLOSED) || $courses_of_user[$course['code']]['status'] == '1' || api_is_platform_admin()) {
                    $courses_list_string .= "</a></b><br />";
                }
                if (api_get_setting('display_coursecode_in_courselist') == 'true') {
                    $courses_list_string .= ' '.$course['visual_code'];
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
//                if (api_get_setting('show_different_course_language') == 'true' && $course['course_language'] != api_get_setting('platformLanguage')) {
//                    $courses_list_string .= ' - ' . $course['course_language'];
//                }
                // We display a subscription link if
                // 1. it is allowed to register for the course and if the course is not already in the courselist of the user and if the user is identiefied
                // 2               
                if ($user_identified && !key_exists($course['code'], $courses_of_user)) {                   
                    if ($course['subscribe'] == '1') {
                        
                        $_SESSION["display_confirmation_message"] = get_lang('langSubscribed');
                        $courses_list_string .= "<form class ='subscribeForm' action='main/auth/courses.php?action=subscribe&amp;category=" . $_GET['category'] . "&origin=index' method='POST'>";
                        $courses_list_string .= '<input type="hidden" name="sec_token" value="' . $stok . '">';
                        $courses_list_string .= "<input type=\"hidden\" name=\"subscribe\" value=\"" . $course['code'] . "\" />";
                        $courses_list_string .= "<input type=\"image\" name=\"unsub\" src=\"".  api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets')."/images/action/subscribe.png\" alt=\"" . get_lang("Subscribe") . "\" /><span>" . get_lang("Subscribe") . "</span><div class='clear'></div></form>";
                        
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
    echo "<a href=\"main/create_course/add_course.php\">
           <div class='b-courselist buttonLinkHome' >
                    <div style='overflow:hidden; height:40px; float:left; background-position: 5px -5px; ' " . Display::return_icon('pixel.gif', get_lang('CourseCreate'), array('class' => 'homepage_button homepage_create_course', 'align' => 'middle', 'style' => 'float:left')) . '
                    </div>
                    <div class="btn--icon" style="float:left;margin-top:15px;width:100%;"><i class="icon-board"></i> &nbsp;&nbsp;' . get_lang('CourseCreate') . "</div>
                <div class='clear'></div>
           </div>
          </a>";
}
/**
 * Enter description here...
 *
 */
function display_edit_course_list_links_tablet() {
    echo "<a href=\"main/auth/courses.php\">" . Display::return_icon('pixel.gif', get_lang('SortMyCourses'), array('class' => 'homepage_button homepage_catalogue', 'align' => 'middle', 'style' => 'float:left')) . '<div class="tablet_section_left_link">' . get_lang('SortMyCourses') . "</div></a>";
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
    $trainers_manual_link = 'http://www.dokeos.com/wordpress/wp-content/uploads/2014/03/DokeosManagerTrainersManual-v3.pdf';
    if ($user_selected_language == "french") {
        //$trainers_manual_link = 'http://www.dokeos.com/wordpress/wp-content/uploads/2014/03/DokeosManagerTrainersManual-v3.pdf';
	$trainers_manual_link = 'http://www.dokeos.com/wordpress/wp-content/uploads/2014/11/DOK--ebook.pdf';
}
    echo "  <a href=\"".$trainers_manual_link."\" target=\"_blank\">
                <div class='buttonLinkHome'>
                    <div style='overflow:hidden; height:40px; float:left; background-position: 5px -5px; '" . Display::return_icon('pixel.gif', get_lang('TrainersManual'), array('class' => 'homepage_button homepage_trainers_manual', 'align' => 'middle', 'style' => 'float:left')) . '
                    </div>                        
                    <div class="btn--icon" style="float:left;margin-top:15px;width:100%;"><i class="icon-pdf"></i> &nbsp;&nbsp;' . get_lang('TrainersManual') . " </div>
                <div class='clear'></div>
            </div>
            </a>";
    
//    echo "<a href=\"http://www.dokeos.com/en/video-tutorials/\" target=\"_blank\">" . Display::return_icon('pixel.gif', get_lang('VideoTutorials'), array('class' => 'homepage_button homepage_video_tutorials', 'align' => 'middle', 'style' => 'float:left')) . '<div class="tablet_section_left_link">' . get_lang('VideoTutorials') . "</div></a>";
    echo "<a href=\"" . $trainers_training_link . "\" target=\"_blank\">
                <div class='buttonLinkHome'>
                        <div style='overflow:hidden; height:40px; float:left; background-position: 5px -5px; '" . Display::return_icon('pixel.gif', get_lang('TrainersTraining'), array('class' => 'homepage_button homepage_trainers_training', 'align' => 'middle', 'style' => 'float:left')) . '
                        </div>
                        <div class="btn--icon" style="float:left;margin-top:15px;width:100%;"><i class="icon-group"></i> &nbsp;&nbsp;' . get_lang('TrainersTraining') . "</div>
                <div class='clear'></div>
          </div>
          </a>";
}
?>
<div id="portalExpiredMessageAdmin" style="display: none;">
    
    <?php echo Display :: display_icon('avatar_error_message.png'); ?>
    <h3 style="text-align: center;display:inline;position:absolute;left:120px;top:9px;width:480px">           
         <?php 
               echo get_lang("YourPortalHasExpiredYouCanUpgradeYourPortalByClicking")." "."<a id='linkToSendMessage' style='color:#FF0000;' href='#'>".  api_strtolower(get_lang('Here'))."</a>";
          ?>
</div>
<div id ="portalExpiredMessageOthers" style="display: none;">
     <?php echo Display :: display_icon('avatar_error_message.png'); ?>
    <h3 style="text-align: center;display:inline;position:absolute;left:120px;top:9px;width:480px">           
        <?php echo get_lang("ThisPortalHasExpiredPleaseContactYourAdministrationForMoreInformation"); ?>    
    </h3>
</div>
    <?php
