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
            'my-setting-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    public function create_admin_page() {
        // Set class property
        $this->options = get_option( 'firewatch_options' );
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>Fire Watch</h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'firewatch_option_group' );   
                do_settings_sections( 'my-setting-admin' );
                submit_button(); 
            ?>
            </form>
        </div>
        <div>
            <h2>Info</h2>
            <h4>Values to enter for CFA Districts</h4>
            <table style="border:1px solid black">
                <tr style="margin:5px 35px;">
                    <th style="margin:5px 35px;">District</th>
                    <th>Value</th>
                </tr>
                <tr style="margin:5px 35px;">
                    <td>Cental District</td>
                    <td>central</td>
                </tr>
                <tr style="margin:5px 35px;">
                    <td>East Gippsland Region</td>
                    <td>eastgippsland</td>
                </tr>
                <tr style="margin:5px 35px;">
                    <td>Mallee Region</td>
                    <td>mallee</td>
                </tr>
                <tr style="margin:5px 35px;">
                    <td>North Central District</td>
                    <td>northcentral</td>
                </tr>
                <tr style="margin:5px 35px;">
                    <td>North East District</td>
                    <td>northeast</td>
                </tr>
                <tr style="margin:5px 35px;">
                    <td>Northern Country District</td>
                    <td>northerncountry</td>
                </tr>
                <tr style="margin:5px 35px;">
                    <td>SouthWest District</td>
                    <td>southwest</td>
                </tr>
                <tr style="margin:5px 35px;">
                    <td>West and South Gippsland Region</td>
                    <td>westandsouthgippsland</td>
                </tr>
                <tr style="margin:5px 35px;">
                    <td>Wimera Region</td>
                    <td>wimera</td>
                </tr>
            </table>
        </div>
        <?php
    }

    /* Register and add settings */
    public function page_init() {
        register_setting(
            'firewatch_option_group', // Option group
            'firewatch_options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Options', // Title
            array( $this, 'print_section_info' ), // Callback
            'my-setting-admin' // Page
        );  

        add_settings_field(
            'woeid', 
            'WOE ID', 
            array( $this, 'woeid_callback' ), 
            'my-setting-admin', 
            'setting_section_id'
        );

        add_settings_field(
            'cfa_district', 
            'CFA District', 
            array( $this, 'cfa_district_callback' ), 
            'my-setting-admin', 
            'setting_section_id'
        );

        add_settings_field(
            'bom_area', 
            'BOM Area', 
            array( $this, 'bom_area_callback' ), 
            'my-setting-admin', 
            'setting_section_id'
        );

        add_settings_field(
            'twitter_timeline', 
            'Twitter Timeline HTML', 
            array( $this, 'twitter_timeline_callback' ), 
            'my-setting-admin', 
            'setting_section_id'
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
            $new_input['cfa_district'] = sanitize_text_field( $input['cfa_district'] );

        if( isset( $input['bom_area'] ) )
            $new_input['bom_area'] = sanitize_text_field( $input['bom_area'] );

        if( isset( $input['twitter_timeline'] ) )
            $new_input['twitter_timeline'] = $input['twitter_timeline'];

        return $new_input;
    }

    /* Print the Section text */
    public function print_section_info() {
        print '';
    }

    /* Get the settings option array and print one of its values */
    public function woeid_callback() {
        printf(
            '<input type="text" id="woeid" name="firewatch_options[woeid]" value="%s" />',
            isset( $this->options['woeid'] ) ? esc_attr( $this->options['woeid']) : ''
        );
    }

    /* Get the settings option array and print one of its values */
    public function bom_area_callback() {
        printf(
            '<input type="text" id="bom_area" name="firewatch_options[bom_area]" value="%s" />',
            isset( $this->options['bom_area'] ) ? esc_attr( $this->options['bom_area']) : ''
        );
    }

    /* Get the settings option array and print one of its values */
    public function cfa_district_callback() {
        printf(
            // '<select name="cfa_district">
            //     <option value="central">Central District</option>
            //     <option value="eastgippsland">East Gippsland Region</option>
            //     <option value="mallee">Mallee Region</option>
            //     <option value="northcentral">North Central District</option>
            //     <option value="northeast">North East District</option>
            //     <option value="northerncountry">Northern Country District</option>
            //     <option value="southwest">SouthWest District</option>
            //     <option value="westandsouthgippsland">West and South Gippsland Region</option>
            //     <option value="wimera">Wimera Region</option>
            // </select>' 
            '<input type="text" id="cfa_district" name="firewatch_options[cfa_district]" value="%s" />',
            isset( $this->options['cfa_district'] ) ? esc_attr( $this->options['cfa_district']) : ''
        );
    }

    /* Get the settings option array and print one of its values */
    public function twitter_timeline_callback() {
        printf('<textarea id="twitter_widget_id" name="firewatch_options[twitter_timeline]">');
        printf(isset( $this->options['twitter_timeline'] ) ? esc_attr( $this->options['twitter_timeline']) : '');
        printf('</textarea>');
        printf('<a href="https://dev.twitter.com/docs/embedded-timelines" target="_blank">Generating Twitter Embedded Timelines</a>');
    }
}

if( is_admin() )
    $fire_watch_settings = new FireWatchSettingsPage();
