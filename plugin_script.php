<?php
/*
Plugin Name: Mixpanel Tracker
Description: Tracks all button clicks, menu clicks, linked images, and landing page visits using Mixpanel.
Version: 1.2
Author: N.B.Ryttel
Author URI: https://github.com/ninryt
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class MixpanelTracker {
    private $mixpanel_token;

    public function __construct() {
        // Get token from WordPress options, fallback to empty string if not set
        $this->mixpanel_token = get_option('mixpanel_token', '');
        
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_footer', array($this, 'add_tracking_code'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    // Add admin menu for token configuration
    public function add_admin_menu() {
        add_options_page(
            'Mixpanel Settings',
            'Mixpanel',
            'manage_options',
            'mixpanel-settings',
            array($this, 'settings_page')
        );
    }

    // Create the settings page
    public function settings_page() {
        if (isset($_POST['mixpanel_token'])) {
            update_option('mixpanel_token', sanitize_text_field($_POST['mixpanel_token']));
            $this->mixpanel_token = get_option('mixpanel_token');
            echo '<div class="updated"><p>Settings saved!</p></div>';
        }
        ?>
        <div class="wrap">
            <h2>Mixpanel Settings</h2>
            <form method="post">
                <table class="form-table">
                    <tr>
                        <th scope="row">Mixpanel Token</th>
                        <td>
                            <input type="text" name="mixpanel_token" value="<?php echo esc_attr($this->mixpanel_token); ?>" class="regular-text">
                            <p class="description">Enter your Mixpanel project token here</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function enqueue_scripts() {
        // Enqueue Mixpanel library
        wp_enqueue_script('mixpanel', 'https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js', array(), null, true);
    }

    public function add_tracking_code() {
        ?>
        <script>
        (function(f,b){if(!b.__SV){var e,g,i,h;window.mixpanel=b;b._i=[];b.init=function(e,f,c){function g(a,d){var b=d.split(".");2==b.length&&(a=a[b[0]],d=b[1]);a[d]=function(){a.push([d].concat(Array.prototype.slice.call(arguments,0)))}}var a=b;"undefined"!==typeof c?a=b[c]=[]:c="mixpanel";a.people=a.people||[];a.toString=function(a){var d="mixpanel";"mixpanel"!==c&&(d+="."+c);a||(d+=" (stub)");return d};a.people.toString=function(){return a.toString(1)+".people (stub)"};i="disable time_event track track_pageview track_links track_forms track_with_groups add_group set_group remove_group register register_once alias unregister identify name_tag set_config reset opt_in_tracking opt_out_tracking has_opted_in_tracking has_opted_out_tracking clear_opt_in_out_tracking start_batch_senders people.set people.set_once people.unset people.increment people.append people.union people.track_charge people.clear_charges people.delete_user people.remove".split(" ");
        for(h=0;h<i.length;h++)g(a,i[h]);var j="set set_once union unset remove delete".split(" ");a.get_group=function(){function b(c){d[c]=function(){call2_args=arguments;call2=[c].concat(Array.prototype.slice.call(arguments,0));a.push([e,call2])}}var d={},e=["get_group"].concat(Array.prototype.slice.call(arguments,0));for(h=0;h<j.length;h++)b(j[h]);return d};b._i.push([e,f,c])};b.__SV=1.2;e=f.createElement("script");e.type="text/javascript";e.async=!0;e.src="undefined"!==typeof MIXPANEL_CUSTOM_LIB_URL?
        MIXPANEL_CUSTOM_LIB_URL:"file:"===f.location.protocol&&"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js".match(/^\/\//)?"https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js":"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js";g=f.getElementsByTagName("script")[0];g.parentNode.insertBefore(e,g)}})(document,window.mixpanel||[]);

        // Initialize Mixpanel with your token
        mixpanel.init('<?php echo esc_js($this->mixpanel_token); ?>', {
            debug: false,  // Disable debug mode
            track_pageview: false,
            ignore_dnt: true
        });

        // Track page views
        mixpanel.track('Page View', {
            'page': window.location.pathname,
            'title': document.title,
            'url': window.location.href
        });

        // Click event handling
        document.addEventListener('click', function(e) {
            let target = e.target;
            
            if (target.tagName === 'IMG') {
                target = target.closest('a') || target;
            }
            
            let trackingData = {
                'element_type': target.tagName,
                'text': target.innerText || target.textContent || '',
                'url': target.href || window.location.href,
                'id': target.id || '',
                'classes': target.className || ''
            };

            if (target.tagName === 'A') {
                mixpanel.track('Link Click', trackingData);
            } else if (target.tagName === 'BUTTON') {
                mixpanel.track('Button Click', trackingData);
            }
        });
        </script>
        <?php
    }
}

// Initialize the tracker
new MixpanelTracker();
