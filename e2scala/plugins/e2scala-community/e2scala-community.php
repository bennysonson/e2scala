<?php

/**
 * Plugin Name: E2SCALA Community
 * Description: Allows users to see an interactive map with all of the users on the map. Users can be filtered based on
 *              name, expertise, or country
 * Version: 2.0.0
 * Author: Chris Tong, Benson Liu
 * License: GPLv2
 */

defined('ABSPATH') or die;

class E2SCALACommunity
{

    public function __construct()
    {
        add_shortcode('e2scala-user-map', array($this, 'display_user_map'));
        add_shortcode('e2scala-user-search', array($this, 'display_user_search'));
        add_shortcode('e2scala-working-groups-list', array($this, 'display_working_group_list'));
    }

    public function activate()
    {
        // You would use this to provide a function to set up your plugin — for example, creating some default settings in the options table.
        flush_rewrite_rules();
    }

    public function deactivate()
    {
        // You would use this to provide a function that clears any temporary data stored by your plugin.
    }

    public function get_user_data($user)
    {
        $user_name = get_userdata($user->get('ID'))->get('display_name');
        $user_email = get_userdata($user->get('ID'))->get('user_email');
        $user_pronouns = get_metadata('user', $user->get('ID'), 'user_registration_pronouns')[0];
        $user_country = get_metadata('user', $user->get('ID'), 'user_registration_country')[0];
        $user_affiliation = get_metadata('user', $user->get('ID'), 'user_registration_affiliation')[0];
        $user_expertise = implode(", ", get_metadata('user', $user->get('ID'), 'user_registration_expertise')[0]);
        $user_topics = implode(", ", get_metadata('user', $user->get('ID'), 'user_registration_topics_of_interest')[0]);
        $user_wg_ids = get_metadata('user', $user->get('ID'), 'working-group');
        $user_wgs = [];
        foreach ($user_wg_ids as $wg) {
            if (get_post(intval($wg))) {
                $wg_name = get_post(intval($wg))->post_title;
                array_push($user_wgs, $wg_name);
            }
        }
        $user_working_groups = implode(", ", $user_wgs);
        if ($user_working_groups == "") {
            $user_working_groups = "None";
        }
        $user_data = array(
            "user_name" => $user_name,
            "user_email" => $user_email,
            "user_pronouns" => $user_pronouns,
            "user_country" => $user_country,
            "user_affiliation" => $user_affiliation,
            "user_expertise" => $user_expertise,
            "user_topics" => $user_topics,
            "user_working_groups" => $user_working_groups,
        );
        return $user_data;
    }

    public function display_working_group_list()
    {
        ob_start();

        // Need to get all working groups
        $all_working_groups = get_posts(['post_type' => 'working_group']);

        $chair_label = "";
        $created_on_label = "";
        $number_members_label = "";
        $countries_represented_label = "";

        if (get_locale() == "en_US") {
            $chair_label = "Chair";
            $number_members_label = "members";
            $countries_represented_label = "Countries Represented: ";
        } else if (get_locale() == "es_ES") {
            $chair_label = "Líder";
            $number_members_label = "miembros";
            $countries_represented_label = "Paises Representados: ";
        }

        ?>
        <div>
            <?php
                foreach ($all_working_groups as $working_group) {
                    $wg = $working_group->to_array();

                    //Get data for working groups display
                    $post_meta = get_post_meta($wg['ID']);

                    //Chair
                    $chairId = $post_meta['chairs'][0];
                    $chair = get_user_by('ID', $chairId)->get('display_name');

                    //Number of members and member names
                    $membersId = $post_meta['members'];
                    $membersArray = [];
                    $numbMembers = count($membersId);
                    foreach ($membersId as $memberId) {
                        $memberName = get_user_by('ID', $memberId)->get('display_name');
                        array_push($membersArray, $memberName);
                    }

                    $numbMembersString = $numbMembers . ' ' . $number_members_label;
                    $membersString = implode(', ', $membersArray);

                    //Countries represented
                    $countriesArray = [];
                    foreach($membersId as $memberId) {
                        $memberCountry = get_user_by('ID', $memberId)->get('user_registration_country');
                        if ($memberCountry != '' && !in_array($memberCountry, $countriesArray)) {
                            array_push($countriesArray, $memberCountry);
                        }
                    }

                    $countriesString = $countries_represented_label . implode(', ', $countriesArray);
            ?>
                <div style='justify-content: start;'>
                    <a href="<?=$wg['post_name']?>">
                        <div class="working-group">
                        <h3><?=$wg['post_title']?></h3>
                            <h4><?=$chair_label?>: <?=$chair?></h4>
                            <h5><?=$numbMembersString?></h5>
                            <h6><?=$membersString?></h6>
                            <h5><?=$countriesString?></h5>
                        </div>
                    </a>
                </div>
                <hr>
                <?php
                }
                ?>
        </div>
        <?php
        return ob_get_clean();
    }
    

    public function display_user_map()
    {
        $users = get_users();

        ob_start();

        echo do_shortcode("[leaflet-map fitbounds zoomcontrol scrollzoom]");

        // Loop through all users and add a marker for each user
        foreach ($users as $user) {
            $address = get_metadata('user', $user->get('ID'), 'user_registration_country');
            if (count($address) > 0) {
                echo do_shortcode("[leaflet-marker address='" . $address[0] . "']");
            }
        }

        echo do_shortcode("[cluster]");
        echo do_shortcode("[zoomhomemap]");
        return ob_get_clean();
    }

    public function display_user_search()
    {
        $users = get_users();
        $name_label = "";
        $email_label = "";
        $pronouns_label = "";
        $country_label = "";
        $affiliation_label = "";
        $expertise_label = "";
        $topics_of_interest_label = "";
        $working_groups_label = "";

        if (get_locale() == 'en_US') {
            $name_label = "Name";
            $email_label = "Email";
            $pronouns_label = "Pronouns";
            $country_label = "Country";
            $affiliation_label = "Affiliation";
            $expertise_label = "Expertise";
            $topics_of_interest_label = "Topics of Interest";
            $working_groups_label = "Working Groups";
        } else if (get_locale() == 'es_ES') {
            $name_label = "Nombre";
            $email_label = "Correo Electrónico";
            $pronouns_label = "Pronombres";
            $country_label = "La Región";
            $affiliation_label = "Afiliación";
            $expertise_label = "Pericia";
            $topics_of_interest_label = "Temas de Interés";
            $working_groups_label = "Grupos de Trabajo";
        }

        ob_start();
        ?>

        <form action="<?php echo $PHP_SELF; ?>" method="post" id="myform">
            Name:<input type="text" name="filterName" id="filterName">
            &nbsp;Country:
            <select form="myform" name="filterCountry" id="filterCountry">
            <option label="Select a country ... " selected="selected"></option>
        <optgroup id="country-optgroup-Africa" label="Africa">
            <option value="DZ" label="Algeria">Algeria</option>
            <option value="AO" label="Angola">Angola</option>
            <option value="BJ" label="Benin">Benin</option>
            <option value="BW" label="Botswana">Botswana</option>
            <option value="BF" label="Burkina Faso">Burkina Faso</option>
            <option value="BI" label="Burundi">Burundi</option>
            <option value="CM" label="Cameroon">Cameroon</option>
            <option value="CV" label="Cape Verde">Cape Verde</option>
            <option value="CF" label="Central African Republic">Central African Republic</option>
            <option value="TD" label="Chad">Chad</option>
            <option value="KM" label="Comoros">Comoros</option>
            <option value="CG" label="Congo - Brazzaville">Congo - Brazzaville</option>
            <option value="CD" label="Congo - Kinshasa">Congo - Kinshasa</option>
            <option value="CI" label="Côte d’Ivoire">Côte d’Ivoire</option>
            <option value="DJ" label="Djibouti">Djibouti</option>
            <option value="EG" label="Egypt">Egypt</option>
            <option value="GQ" label="Equatorial Guinea">Equatorial Guinea</option>
            <option value="ER" label="Eritrea">Eritrea</option>
            <option value="ET" label="Ethiopia">Ethiopia</option>
            <option value="GA" label="Gabon">Gabon</option>
            <option value="GM" label="Gambia">Gambia</option>
            <option value="GH" label="Ghana">Ghana</option>
            <option value="GN" label="Guinea">Guinea</option>
            <option value="GW" label="Guinea-Bissau">Guinea-Bissau</option>
            <option value="KE" label="Kenya">Kenya</option>
            <option value="LS" label="Lesotho">Lesotho</option>
            <option value="LR" label="Liberia">Liberia</option>
            <option value="LY" label="Libya">Libya</option>
            <option value="MG" label="Madagascar">Madagascar</option>
            <option value="MW" label="Malawi">Malawi</option>
            <option value="ML" label="Mali">Mali</option>
            <option value="MR" label="Mauritania">Mauritania</option>
            <option value="MU" label="Mauritius">Mauritius</option>
            <option value="YT" label="Mayotte">Mayotte</option>
            <option value="MA" label="Morocco">Morocco</option>
            <option value="MZ" label="Mozambique">Mozambique</option>
            <option value="NA" label="Namibia">Namibia</option>
            <option value="NE" label="Niger">Niger</option>
            <option value="NG" label="Nigeria">Nigeria</option>
            <option value="RW" label="Rwanda">Rwanda</option>
            <option value="RE" label="Réunion">Réunion</option>
            <option value="SH" label="Saint Helena">Saint Helena</option>
            <option value="SN" label="Senegal">Senegal</option>
            <option value="SC" label="Seychelles">Seychelles</option>
            <option value="SL" label="Sierra Leone">Sierra Leone</option>
            <option value="SO" label="Somalia">Somalia</option>
            <option value="ZA" label="South Africa">South Africa</option>
            <option value="SD" label="Sudan">Sudan</option>
            <option value="SZ" label="Swaziland">Swaziland</option>
            <option value="ST" label="São Tomé and Príncipe">São Tomé and Príncipe</option>
            <option value="TZ" label="Tanzania">Tanzania</option>
            <option value="TG" label="Togo">Togo</option>
            <option value="TN" label="Tunisia">Tunisia</option>
            <option value="UG" label="Uganda">Uganda</option>
            <option value="EH" label="Western Sahara">Western Sahara</option>
            <option value="ZM" label="Zambia">Zambia</option>
            <option value="ZW" label="Zimbabwe">Zimbabwe</option>
        </optgroup>
        <optgroup id="country-optgroup-Americas" label="Americas">
            <option value="AI" label="Anguilla">Anguilla</option>
            <option value="AG" label="Antigua and Barbuda">Antigua and Barbuda</option>
            <option value="AR" label="Argentina">Argentina</option>
            <option value="AW" label="Aruba">Aruba</option>
            <option value="BS" label="Bahamas">Bahamas</option>
            <option value="BB" label="Barbados">Barbados</option>
            <option value="BZ" label="Belize">Belize</option>
            <option value="BM" label="Bermuda">Bermuda</option>
            <option value="BO" label="Bolivia">Bolivia</option>
            <option value="BR" label="Brazil">Brazil</option>
            <option value="VG" label="British Virgin Islands">British Virgin Islands</option>
            <option value="CA" label="Canada">Canada</option>
            <option value="KY" label="Cayman Islands">Cayman Islands</option>
            <option value="CL" label="Chile">Chile</option>
            <option value="CO" label="Colombia">Colombia</option>
            <option value="CR" label="Costa Rica">Costa Rica</option>
            <option value="CU" label="Cuba">Cuba</option>
            <option value="DM" label="Dominica">Dominica</option>
            <option value="DO" label="Dominican Republic">Dominican Republic</option>
            <option value="EC" label="Ecuador">Ecuador</option>
            <option value="SV" label="El Salvador">El Salvador</option>
            <option value="FK" label="Falkland Islands">Falkland Islands</option>
            <option value="GF" label="French Guiana">French Guiana</option>
            <option value="GL" label="Greenland">Greenland</option>
            <option value="GD" label="Grenada">Grenada</option>
            <option value="GP" label="Guadeloupe">Guadeloupe</option>
            <option value="GT" label="Guatemala">Guatemala</option>
            <option value="GY" label="Guyana">Guyana</option>
            <option value="HT" label="Haiti">Haiti</option>
            <option value="HN" label="Honduras">Honduras</option>
            <option value="JM" label="Jamaica">Jamaica</option>
            <option value="MQ" label="Martinique">Martinique</option>
            <option value="MX" label="Mexico">Mexico</option>
            <option value="MS" label="Montserrat">Montserrat</option>
            <option value="AN" label="Netherlands Antilles">Netherlands Antilles</option>
            <option value="NI" label="Nicaragua">Nicaragua</option>
            <option value="PA" label="Panama">Panama</option>
            <option value="PY" label="Paraguay">Paraguay</option>
            <option value="PE" label="Peru">Peru</option>
            <option value="PR" label="Puerto Rico">Puerto Rico</option>
            <option value="BL" label="Saint Barthélemy">Saint Barthélemy</option>
            <option value="KN" label="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
            <option value="LC" label="Saint Lucia">Saint Lucia</option>
            <option value="MF" label="Saint Martin">Saint Martin</option>
            <option value="PM" label="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>
            <option value="VC" label="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines</option>
            <option value="SR" label="Suriname">Suriname</option>
            <option value="TT" label="Trinidad and Tobago">Trinidad and Tobago</option>
            <option value="TC" label="Turks and Caicos Islands">Turks and Caicos Islands</option>
            <option value="VI" label="U.S. Virgin Islands">U.S. Virgin Islands</option>
            <option value="US" label="United States">United States</option>
            <option value="UY" label="Uruguay">Uruguay</option>
            <option value="VE" label="Venezuela">Venezuela</option>
        </optgroup>
        <optgroup id="country-optgroup-Asia" label="Asia">
            <option value="AF" label="Afghanistan">Afghanistan</option>
            <option value="AM" label="Armenia">Armenia</option>
            <option value="AZ" label="Azerbaijan">Azerbaijan</option>
            <option value="BH" label="Bahrain">Bahrain</option>
            <option value="BD" label="Bangladesh">Bangladesh</option>
            <option value="BT" label="Bhutan">Bhutan</option>
            <option value="BN" label="Brunei">Brunei</option>
            <option value="KH" label="Cambodia">Cambodia</option>
            <option value="CN" label="China">China</option>
            <option value="GE" label="Georgia">Georgia</option>
            <option value="HK" label="Hong Kong SAR China">Hong Kong SAR China</option>
            <option value="IN" label="India">India</option>
            <option value="ID" label="Indonesia">Indonesia</option>
            <option value="IR" label="Iran">Iran</option>
            <option value="IQ" label="Iraq">Iraq</option>
            <option value="IL" label="Israel">Israel</option>
            <option value="JP" label="Japan">Japan</option>
            <option value="JO" label="Jordan">Jordan</option>
            <option value="KZ" label="Kazakhstan">Kazakhstan</option>
            <option value="KW" label="Kuwait">Kuwait</option>
            <option value="KG" label="Kyrgyzstan">Kyrgyzstan</option>
            <option value="LA" label="Laos">Laos</option>
            <option value="LB" label="Lebanon">Lebanon</option>
            <option value="MO" label="Macau SAR China">Macau SAR China</option>
            <option value="MY" label="Malaysia">Malaysia</option>
            <option value="MV" label="Maldives">Maldives</option>
            <option value="MN" label="Mongolia">Mongolia</option>
            <option value="MM" label="Myanmar [Burma]">Myanmar [Burma]</option>
            <option value="NP" label="Nepal">Nepal</option>
            <option value="NT" label="Neutral Zone">Neutral Zone</option>
            <option value="KP" label="North Korea">North Korea</option>
            <option value="OM" label="Oman">Oman</option>
            <option value="PK" label="Pakistan">Pakistan</option>
            <option value="PS" label="Palestinian Territories">Palestinian Territories</option>
            <option value="YD" label="People's Democratic Republic of Yemen">People's Democratic Republic of Yemen</option>
            <option value="PH" label="Philippines">Philippines</option>
            <option value="QA" label="Qatar">Qatar</option>
            <option value="SA" label="Saudi Arabia">Saudi Arabia</option>
            <option value="SG" label="Singapore">Singapore</option>
            <option value="KR" label="South Korea">South Korea</option>
            <option value="LK" label="Sri Lanka">Sri Lanka</option>
            <option value="SY" label="Syria">Syria</option>
            <option value="TW" label="Taiwan">Taiwan</option>
            <option value="TJ" label="Tajikistan">Tajikistan</option>
            <option value="TH" label="Thailand">Thailand</option>
            <option value="TL" label="Timor-Leste">Timor-Leste</option>
            <option value="TR" label="Turkey">Turkey</option>
            <option value="TM" label="Turkmenistan">Turkmenistan</option>
            <option value="AE" label="United Arab Emirates">United Arab Emirates</option>
            <option value="UZ" label="Uzbekistan">Uzbekistan</option>
            <option value="VN" label="Vietnam">Vietnam</option>
            <option value="YE" label="Yemen">Yemen</option>
        </optgroup>
        <optgroup id="country-optgroup-Europe" label="Europe">
            <option value="AL" label="Albania">Albania</option>
            <option value="AD" label="Andorra">Andorra</option>
            <option value="AT" label="Austria">Austria</option>
            <option value="BY" label="Belarus">Belarus</option>
            <option value="BE" label="Belgium">Belgium</option>
            <option value="BA" label="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
            <option value="BG" label="Bulgaria">Bulgaria</option>
            <option value="HR" label="Croatia">Croatia</option>
            <option value="CY" label="Cyprus">Cyprus</option>
            <option value="CZ" label="Czech Republic">Czech Republic</option>
            <option value="DK" label="Denmark">Denmark</option>
            <option value="DD" label="East Germany">East Germany</option>
            <option value="EE" label="Estonia">Estonia</option>
            <option value="FO" label="Faroe Islands">Faroe Islands</option>
            <option value="FI" label="Finland">Finland</option>
            <option value="FR" label="France">France</option>
            <option value="DE" label="Germany">Germany</option>
            <option value="GI" label="Gibraltar">Gibraltar</option>
            <option value="GR" label="Greece">Greece</option>
            <option value="GG" label="Guernsey">Guernsey</option>
            <option value="HU" label="Hungary">Hungary</option>
            <option value="IS" label="Iceland">Iceland</option>
            <option value="IE" label="Ireland">Ireland</option>
            <option value="IM" label="Isle of Man">Isle of Man</option>
            <option value="IT" label="Italy">Italy</option>
            <option value="JE" label="Jersey">Jersey</option>
            <option value="LV" label="Latvia">Latvia</option>
            <option value="LI" label="Liechtenstein">Liechtenstein</option>
            <option value="LT" label="Lithuania">Lithuania</option>
            <option value="LU" label="Luxembourg">Luxembourg</option>
            <option value="MK" label="Macedonia">Macedonia</option>
            <option value="MT" label="Malta">Malta</option>
            <option value="FX" label="Metropolitan France">Metropolitan France</option>
            <option value="MD" label="Moldova">Moldova</option>
            <option value="MC" label="Monaco">Monaco</option>
            <option value="ME" label="Montenegro">Montenegro</option>
            <option value="NL" label="Netherlands">Netherlands</option>
            <option value="NO" label="Norway">Norway</option>
            <option value="PL" label="Poland">Poland</option>
            <option value="PT" label="Portugal">Portugal</option>
            <option value="RO" label="Romania">Romania</option>
            <option value="RU" label="Russia">Russia</option>
            <option value="SM" label="San Marino">San Marino</option>
            <option value="RS" label="Serbia">Serbia</option>
            <option value="CS" label="Serbia and Montenegro">Serbia and Montenegro</option>
            <option value="SK" label="Slovakia">Slovakia</option>
            <option value="SI" label="Slovenia">Slovenia</option>
            <option value="ES" label="Spain">Spain</option>
            <option value="SJ" label="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option>
            <option value="SE" label="Sweden">Sweden</option>
            <option value="CH" label="Switzerland">Switzerland</option>
            <option value="UA" label="Ukraine">Ukraine</option>
            <option value="SU" label="Union of Soviet Socialist Republics">Union of Soviet Socialist Republics</option>
            <option value="GB" label="United Kingdom">United Kingdom</option>
            <option value="VA" label="Vatican City">Vatican City</option>
            <option value="AX" label="Åland Islands">Åland Islands</option>
        </optgroup>
        <optgroup id="country-optgroup-Oceania" label="Oceania">
            <option value="AS" label="American Samoa">American Samoa</option>
            <option value="AQ" label="Antarctica">Antarctica</option>
            <option value="AU" label="Australia">Australia</option>
            <option value="BV" label="Bouvet Island">Bouvet Island</option>
            <option value="IO" label="British Indian Ocean Territory">British Indian Ocean Territory</option>
            <option value="CX" label="Christmas Island">Christmas Island</option>
            <option value="CC" label="Cocos [Keeling] Islands">Cocos [Keeling] Islands</option>
            <option value="CK" label="Cook Islands">Cook Islands</option>
            <option value="FJ" label="Fiji">Fiji</option>
            <option value="PF" label="French Polynesia">French Polynesia</option>
            <option value="TF" label="French Southern Territories">French Southern Territories</option>
            <option value="GU" label="Guam">Guam</option>
            <option value="HM" label="Heard Island and McDonald Islands">Heard Island and McDonald Islands</option>
            <option value="KI" label="Kiribati">Kiribati</option>
            <option value="MH" label="Marshall Islands">Marshall Islands</option>
            <option value="FM" label="Micronesia">Micronesia</option>
            <option value="NR" label="Nauru">Nauru</option>
            <option value="NC" label="New Caledonia">New Caledonia</option>
            <option value="NZ" label="New Zealand">New Zealand</option>
            <option value="NU" label="Niue">Niue</option>
            <option value="NF" label="Norfolk Island">Norfolk Island</option>
            <option value="MP" label="Northern Mariana Islands">Northern Mariana Islands</option>
            <option value="PW" label="Palau">Palau</option>
            <option value="PG" label="Papua New Guinea">Papua New Guinea</option>
            <option value="PN" label="Pitcairn Islands">Pitcairn Islands</option>
            <option value="WS" label="Samoa">Samoa</option>
            <option value="SB" label="Solomon Islands">Solomon Islands</option>
            <option value="GS" label="South Georgia and the South Sandwich Islands">South Georgia and the South Sandwich Islands</option>
            <option value="TK" label="Tokelau">Tokelau</option>
            <option value="TO" label="Tonga">Tonga</option>
            <option value="TV" label="Tuvalu">Tuvalu</option>
            <option value="UM" label="U.S. Minor Outlying Islands">U.S. Minor Outlying Islands</option>
            <option value="VU" label="Vanuatu">Vanuatu</option>
            <option value="WF" label="Wallis and Futuna">Wallis and Futuna</option>
        </optgroup>
    </select>
            </select>
            &nbsp;Expertise:
            <select form="myform" name="filterExpertise" id="filterExpertise">
                <option value=""></option>
                <option value="Engineering Seismology">Engineering Seismology</option>
                <option value="Geology">Geology</option>
                <option value="Geophysics">Geophysics</option>
                <option value="Geotechnical Engineering">Geotechnical Engineering</option>
                <option value="Natural Hazards">Natural Hazards</option>
                <option value="Structural Engineering">Structural Engineering</option>
                <option value="Urban Planning">Urban Planning</option>
            </select>
            <br><br>
            &nbsp;Other Expertise:
            <input type="text" name="filterExpertise2" id="filterExpertise2">
            <br><br>
            <input type="submit" name="submitFilter" value="Filter">
        </form>
        <br>

        <table class="tablesorter">
            <thead style='background-color: #125783; color: white;'>
                <th><?=$name_label?></th>
                <th><?=$email_label?></th>
                <th><?=$pronouns_label?></th>
                <th><?=$country_label?></th>
                <th><?=$affiliation_label?></th>
                <th><?=$expertise_label?></th>
                <th><?=$topics_of_interest_label?></th>
                <th><?=$working_groups_label?></th>
            </thead>

            <tbody style='background-color: #FFFFFF;'>
            <?php

        //if filter is applied, edit user list to only contain users who have filter items
        if (isset($_POST['filterName']) || isset($_POST['filterExpertise']) || isset($_POST['filterCountry']) || isset($_POST['filterExpertise2'])) {
            $filter_name = $_POST['filterName'];
            $filter_expertise = $_POST['filterExpertise'];
            $filter_expertise2 = $_POST['filterExpertise2'];
            $filter_country = $_POST['filterCountry'];

            //Loop through users
            $imax = count($users);
            for ($i = 0; $i < $imax; $i++) {
                //User i for this loop
                $user_data = $this->get_user_data($users[$i]);

                //Array of expertise
                $expertiseArray = explode(',', $user_data['user_expertise']);
                for ($x = 0; $x < count($expertiseArray); $x++) {
                    $expertiseArray[$x] = trim($expertiseArray[$x]);
                }

                //remove from users list any users that DO NOT match filters
                //name
                if (strlen($filter_name) != 0) {
                    if (stripos($user_data['user_name'], $filter_name) == false) {
                        if (strcasecmp($user_data['user_name'], $filter_name) == 0) {
                            continue;
                        }

                        if (stripos($user_data['user_name'], $filter_name) === 0) {
                            continue;
                        }
                        unset($users[$i]);
                        continue;
                    }
                }

                //expertise
                if (strlen($filter_expertise) != 0 || strlen($filter_expertise2) != 0) {
                    $good = false;
                    foreach ($expertiseArray as $expertise) {
                        if ((strlen($filter_expertise) != 0) && (strcasecmp($filter_expertise, $expertise) == 0)) {
                            $good = true;
                        }
                        if ((strlen($filter_expertise2) != 0) && (strcasecmp($filter_expertise2, $expertise) == 0)) {
                            $good = true;
                        }
                    }
                    if (!$good) {
                        unset($users[$i]);
                        continue;
                    }

                }

                //country
                if (strlen($filter_country) != 0) {
                    if (strcasecmp($user_data['user_country'], $filter_country) != 0) {
                        unset($users[$i]);
                        continue;
                    }
                }
            }

        }
        // Set table
        foreach ($users as $user) {
            $user_data = $this->get_user_data($user);
            ?>
                <tr>
                    <td><?=$user_data['user_name']?></td>
                    <td><?=$user_data['user_email']?></td>
                    <td><?=$user_data['user_pronouns']?></td>
                    <td><?=$user_data['user_country']?></td>
                    <td><?=$user_data['user_affiliation']?></td>
                    <td><?=$user_data['user_expertise']?></td>
                    <td><?=$user_data['user_topics']?></td>
                    <td><?=$user_data['user_working_groups']?></td>
                </tr>
                <?php
}
        ?>
            </tbody>
        </table>
        <?php
return ob_get_clean();
    }
}

if (class_exists('E2SCALACommunity')) {
    $e2scalaCommunity = new E2SCALACommunity();
}

// activation
register_activation_hook(__FILE__, array($e2scalaCommunity, 'activate'));

// deactivation
register_deactivation_hook(__FILE__, array($e2scalaCommunity, 'deactivate'));