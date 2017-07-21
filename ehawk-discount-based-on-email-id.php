<?php
/**
* Plugin Name: Discounts based on Email for WooCommerce
* Description: you can set specific discount for specific user in woocommerce store
* Version: 1.0.0
* Author: extensionhawk
* Author URI: www.xadapter.com
*/


if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
    /*WordPress Menus API.*/
    function ehawk_add_menu_page()
    {
        //add a new menu item. This is a top level menu item i.e., this menu item can have sub menus
        add_menu_page(
            "emaildiscount", //Required. Text in browser title bar when the page associated with this menu item is displayed.
            "e-mail discount", //Required. Text to be displayed in the menu.
            "manage_options", //Required. The required capability of users to access this menu item.
            "ehawk_email_discount", //Required. A unique identifier to identify this menu item.
            "ehawk_add_menu_page_html", //Optional. This callback outputs the content of the page associated with this menu item.
            "dashicons-email-alt" //Optional. The URL to the menu item icon.
        );

    }

    function ehawk_add_menu_page_html()
    {
        ?>
            
            <h1>Main Menu Page</h1>
            <form method="post" action="options.php">
                <?php
               
                    //add_settings_section callback is displayed here. For every new section we need to call settings_fields.
                    settings_fields("header_section");
                   
                    // all the add_settings_field callbacks is displayed here
                    do_settings_sections("ehawk_email_discount");
               
                    // Add the submit button to serialize the options
                    submit_button();
                   
                ?>         
            </form>
        <?php
    }

    //this action callback is triggered when wordpress is ready to add new items to menu.
    add_action("admin_menu", "ehawk_add_menu_page");

    function ehawk_display_header_content_html(){echo "Provide your e-mail adress";}
    
    function ehawk_display_email_element_content_html()
    {
        $items = get_option('xa_email');
        ?>
        <script type="text/javascript">

            function ehawk_addInput(divName){
                var count = document.querySelectorAll('.xa_row').length;
                var newdiv =document.createElement('div');
                newdiv.innerHTML = '<div class="xa_row"><input type="text" name="xa_email['+count+'][email]" id="xa_email['+count+'][email]" value="" required/>' + '<label><b>Enter Discount</b></label>' + '<input type="text" name="xa_email['+count+'][discount]" id="xa_email['+count+'][discount]" value="" required/></div>';
                document.getElementById(divName).appendChild(newdiv);
                count++;
            }
        </script>
        
        <?php 
        echo "<div id='main_div'>";
        if(!empty($items)){
        foreach($items as $index=>$item)
        {
        ?>

        <div class="xa_row">
            <input type="text" name="xa_email[<?php echo $index;?>][email]" id="xa_email[<?php echo $index;?>][email]" value="<?php echo $item['email']; ?>" />
            <label><b>Enter Discount</b></label>
            <input type="text" name="xa_email[<?php echo $index;?>][discount]" id="xa_email[<?php echo $index;?>][discount]" value="<?php echo $item['discount']; ?>" />
        </div>
        <?php }

        }
         
        $index=count($items);
        ?>
        </div>
         <input type="button" value="Add details" onClick="ehawk_addInput('main_div');">
        <?php
    }
    
    //this action is executed after loads its core, after registering all actions, finds out what page to execute and before producing the actual output(before calling any action callback)
    add_action("admin_init", "ehawk_display_options");
    
    function ehawk_display_options()
    {
        //section name, display name, callback to print description of section, page to which section is attached.
        add_settings_section("header_section", "Disount on email", "ehawk_display_header_content_html", "ehawk_email_discount");

        //setting name, display name, callback to print form element, page in which field is displayed, section to which it belongs.
        //last field section is optional.
        add_settings_field("xa_email", "Enter e-mail", "ehawk_display_email_element_content_html", "ehawk_email_discount", "header_section");
        
        //section name, form element name, callback for sanitization
        register_setting("header_section", "xa_email");
    }
    

    add_filter('woocommerce_product_get_price','ehawk_check_price',1,1);
    add_filter('woocommerce_product_variation_get_price','ehawk_check_price',1,1);
    function ehawk_check_price($price){
        $cust_email = wp_get_current_user();

        $data_n = get_option('xa_email');

        if(!empty($data_n))
        {
            foreach($data_n as $data){

                if($cust_email->user_email == $data['email']){

                    $price = $price - (( $price * $data['discount'] ) / 100); 

                }
            }            
        }

        
        return $price;

    }