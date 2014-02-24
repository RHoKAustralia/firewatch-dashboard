<?php
class FireWatchSettingsPage
{
    private $options;
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }
    public function add_plugin_page() {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'Fire Watch', 
            'manage_options', 
            'fire-watch', 
            array( $this, 'create_admin_page' )
        );
    }

    public function create_admin_page() {
        // Set class property
        $this->options = get_option( 'fire_watch_options' );
        ?>
        <style>
            div.shortcode { display:inline-block; background:#fff; padding:15px 20px 17px; margin-bottom:35px; max-width:100%; width:600px; font-size:16px; line-height:1.6; }
            div.detail { font-size:12px; color:#666; margin-top:3px; margin-left:3px; }

            .district-table { border:1px solid #999; padding:5px; }
            .district-table tr { margin:5px 35px; }
            .district-table th { border-bottom:1px solid #ccc; }
        </style>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>Fire Watch</h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'fire_watch_option_group' );   
                do_settings_sections( 'fire-watch' );
                submit_button(); 
            ?>
            </form>
        </div>

        <h2 style="margin-top:30px">Info</h2>

        <div class="shortcode">
            <small>Add the following shortcode on any page or post to render the dashboard:</small><br>
            [fire_watch_content]
        </div>

        <h4>Values to enter for CFA Districts</h4>
        <table class="district-table">
            <tr>
                <th>District</th>
                <th>Value</th>
            </tr>
            <tr>
                <td>Cental District</td><td>central</td>
            </tr>
            <tr>
                <td>East Gippsland Region</td><td>eastgippsland</td>
            </tr>
            <tr>
                <td>Mallee Region</td><td>mallee</td>
            </tr>
            <tr>
                <td>North Central District</td><td>northcentral</td>
            </tr>
            <tr>
                <td>North East District</td><td>northeast</td>
            </tr>
            <tr>
                <td>Northern Country District</td><td>northerncountry</td>
            </tr>
            <tr>
                <td>SouthWest District</td><td>southwest</td>
            </tr>
            <tr>
                <td>West and South Gippsland Region</td><td>westandsouthgippsland</td>
            </tr>
            <tr>
                <td>Wimera Region</td><td>wimera</td>
            </tr>
        </table>
        <?php
    }

    /* Register and add settings */
    public function page_init() {
        register_setting(
            'fire_watch_option_group', // Option group
            'fire_watch_options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'fire_watch_options_id', // ID
            'Options', // Title
            array( $this, 'print_section_info' ), // Callback
            'fire-watch' // Page
        );  

        add_settings_field(
            'woeid', 
            'WOEID', 
            array( $this, 'woeid_callback' ), 
            'fire-watch', 
            'fire_watch_options_id'
        );

        add_settings_field(
            'cfa_district', 
            'CFA District', 
            array( $this, 'cfa_district_callback' ), 
            'fire-watch', 
            'fire_watch_options_id'
        );

        add_settings_field(
            'bom_area', 
            'BOM Area', 
            array( $this, 'bom_area_callback' ), 
            'fire-watch', 
            'fire_watch_options_id'
        );

        add_settings_field(
            'twitter_timeline', 
            'Twitter Timeline HTML', 
            array( $this, 'twitter_timeline_callback' ), 
            'fire-watch', 
            'fire_watch_options_id'
        );
    }

    /* Sanitize each setting field as needed
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input ) {
        $new_input = array();
        if( isset( $input['woeid'] ) )
            $new_input['woeid'] = sanitize_text_field( $input['woeid'] );

        if( isset( $input['cfa_district'] ) )
            $new_input['cfa_district'] = sanitize_text_field( strtolower($input['cfa_district']) );

        if( isset( $input['bom_area'] ) )
            $new_input['bom_area'] = sanitize_text_field( $input['bom_area'] );

        if( isset( $input['twitter_timeline'] ) )
            $new_input['twitter_timeline'] = $input['twitter_timeline'];

        return $new_input;
    }

    /* Print the Section text */
    public function print_section_info() {
        echo '';
    }

    /* Get the settings option array and print one of its values */
    public function woeid_callback() {
        printf(
            '<input type="text" id="woeid" name="fire_watch_options[woeid]" value="%s">',
            isset( $this->options['woeid'] ) ? esc_attr( $this->options['woeid']) : ''
        );
        echo '<div class="detail">To lookup WOEID for your area, <a href="http://woeid.rosselliot.co.nz/" target="_blank">click here</a>.</div>';
    }

    /* Get the settings option array and print one of its values */
    public function cfa_district_callback() {
        printf(
            /*'<select name="cfa_district">
                <option value="central">Central District</option>
                <option value="eastgippsland">East Gippsland Region</option>
                <option value="mallee">Mallee Region</option>
                <option value="northcentral">North Central District</option>
                <option value="northeast">North East District</option>
                <option value="northerncountry">Northern Country District</option>
                <option value="southwest">SouthWest District</option>
                <option value="westandsouthgippsland">West and South Gippsland Region</option>
                <option value="wimera">Wimera Region</option>
            </select>' */
            '<input type="text" id="cfa_district" name="fire_watch_options[cfa_district]" value="%s">',
            isset( $this->options['cfa_district'] ) ? esc_attr( $this->options['cfa_district']) : ''
        );
        echo '<div class="detail">Value in lower case and without spaces. To find your district, <a href="http://www.cfa.vic.gov.au/warnings-restrictions/find-your-fire-district/" target="_blank">click here</a>.</div>';
    }

    /* Get the settings option array and print one of its values */
    public function bom_area_callback() {
        printf(
            '<input type="text" id="bom_area" name="fire_watch_options[bom_area]" value="%s">',
            isset( $this->options['bom_area'] ) ? esc_attr( $this->options['bom_area']) : ''
        );
        echo '<div class="detail">BOM Area is usually the name of the suburb or town with a weather station. To view a map of Victorian weather stations, <a href="http://www.bom.gov.au/vic/forecasts/map7day.shtml" target="_blank">click here</a>.</div>';
    }

    /* Get the settings option array and print one of its values */
    public function twitter_timeline_callback() {
        echo '<textarea id="twitter_widget_id" name="fire_watch_options[twitter_timeline]" style="height:120px; width:500px;">'.$this->options['twitter_timeline'].'</textarea>';
        echo '<div class="detail">To create a timeline, <a href="https://twitter.com/settings/widgets" target="_blank">click here</a>. To learn more, <a href="https://dev.twitter.com/docs/embedded-timelines" target="_blank">click here</a>.</div>';
    }
}

if( is_admin() )
    $fire_watch_settings = new FireWatchSettingsPage();
