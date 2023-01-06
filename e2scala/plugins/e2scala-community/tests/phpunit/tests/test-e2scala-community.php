<?php
require dirname(__FILE__) . "/../../../e2scala-community.php";

class Test_E2scala_Community extends WP_UnitTestCase {

    private $e2scalaCommunity;
    private $workingGroup1;
    private $workingGroup2;
    private $user_1;
    private $user_2;

    protected function setUp(): void
    {

        // Initializes the plugin
        $this->e2scalaCommunity = new E2SCALACommunity();

        register_post_type('working-group');

        // Creates Working Group 1
        $wg1_id = $this->factory->post->create();
        $this->workingGroup1 = get_post($wg1_id);
        $this->workingGroup1->ID = $wg1_id;

        // Creates Working Group 2
        $wg2_id = $this->factory->post->create();
        $this->workingGroup2 = get_post($wg2_id);
        $this->workingGroup2->ID = $wg2_id;
        
        // Creates the first User
        $user_id_1 = $this->factory->user->create();
        $this->user_1 = get_user_by('ID', $user_id_1);
        wp_update_user(array('ID' => $this->user_1->ID, 'display_name' => 'Test User1', 'user_email' => 'test1@email.com'));
        add_metadata('user', $this->user_1->ID, 'user_registration_pronouns', 'He/Him/His');
        add_metadata('user', $this->user_1->ID, 'user_registration_country', 'US');
        add_metadata('user', $this->user_1->ID, 'user_registration_affiliation', 'NCSU');
        add_metadata('user', $this->user_1->ID, 'user_registration_expertise', array('Earthquake Engineering', 'Seismology'));
        add_metadata('user', $this->user_1->ID, 'user_registration_topics_of_interest', array('Site Response Analysis'));

        // Creates the second User
        $user_id_2 = $this->factory->user->create();
        $this->user_2 = get_user_by('ID', $user_id_2);
        wp_update_user(array('ID' => $this->user_2->ID, 'display_name' => 'Test User2', 'user_email' => 'test2@email.com'));
        add_metadata('user', $this->user_2->ID, 'user_registration_pronouns', 'She/Her/Hers');
        add_metadata('user', $this->user_2->ID, 'user_registration_country', 'AR');
        add_metadata('user', $this->user_2->ID, 'user_registration_affiliation', 'GeoQuake');
        add_metadata('user', $this->user_2->ID, 'user_registration_expertise', array('Geotechnical Engineering', 'Seismology'));
        add_metadata('user', $this->user_2->ID, 'user_registration_topics_of_interest', array('Seismic Slope Stability'));
        add_metadata('user', $this->user_2->ID, 'working-group', $this->workingGroup1->ID);
        add_metadata('user', $this->user_2->ID, 'working-group', $this->workingGroup2->ID);
    }

    public function test_members_data() {
        $user_1_data = $this->e2scalaCommunity->get_user_data($this->user_1);
        $this->assertEquals("Test User1", $user_1_data['user_name']);
        $this->assertEquals("test1@email.com", $user_1_data['user_email']);
        $this->assertEquals("He/Him/His", $user_1_data['user_pronouns']);
        $this->assertEquals("US", $user_1_data['user_country']);
        $this->assertEquals("NCSU", $user_1_data['user_affiliation']);
        $this->assertEquals("Earthquake Engineering, Seismology", $user_1_data['user_expertise']);
        $this->assertEquals("Site Response Analysis", $user_1_data['user_topics']);
        $this->assertEquals("None", $user_1_data['user_working_groups']);

        $user_2_data = $this->e2scalaCommunity->get_user_data($this->user_2);
        $this->assertEquals("Test User2", $user_2_data['user_name']);
        $this->assertEquals("test2@email.com", $user_2_data['user_email']);
        $this->assertEquals("She/Her/Hers", $user_2_data['user_pronouns']);
        $this->assertEquals("AR", $user_2_data['user_country']);
        $this->assertEquals("GeoQuake", $user_2_data['user_affiliation']);
        $this->assertEquals("Geotechnical Engineering, Seismology", $user_2_data['user_expertise']);
        $this->assertEquals("Seismic Slope Stability", $user_2_data['user_topics']);
        $this->assertEquals($this->workingGroup1->post_title . ", " . $this->workingGroup2->post_title, $user_2_data['user_working_groups']);
    }
}