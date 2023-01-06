<?php
require dirname(__FILE__) . "/../../../listserv.php";

class Test_E2scala_Listserv extends WP_UnitTestCase {

    private $chair;
    private $member;
    private $nonmember;
    private $workingGroup;
    private $wg_resource;
    private $user_1;
    private $user_2;

    protected function setUp(): void
    {
        
        // Creates the Chair User
        $chair_id = $this->factory->user->create();
        $this->chair = get_user_by('ID', $chair_id);

        // Creates the Member User
        $member_id = $this->factory->user->create();
        $this->member = get_user_by('ID', $member_id);

        // Creates the Non-Member User
        $nonmember_id = $this->factory->user->create();
        $this->nonmember = get_user_by('ID', $nonmember_id);

        // Creates a Resource to be added to the Working Group
        $this->wg_resource = new stdClass();
        $this->wg_resource->ID = 2;
        $this->wg_resource->post_author = 1;
        $this->wg_resource->post_date = current_time( 'mysql' );
        $this->wg_resource->post_date_gmt = current_time( 'mysql', 1 );
        $this->wg_resource->post_title = 'Working Group Resource';
        $this->wg_resource->post_content = 'This is a test working group resource.';
        $this->wg_resource->post_status = 'publish';
        $this->wg_resource->comment_status = 'closed';
        $this->wg_resource->ping_status = 'closed';
        $this->wg_resource->post_name = 'working-group-resource';
        $this->wg_resource->post_type = 'wg_resource';
        $this->wg_resource->filter = 'raw'; // important!

        // Creates the Working Group
        $this->workingGroup = new stdClass();
        $this->workingGroup->ID = 1;
        $this->workingGroup->post_author = 1;
        $this->workingGroup->post_date = current_time( 'mysql' );
        $this->workingGroup->post_date_gmt = current_time( 'mysql', 1 );
        $this->workingGroup->post_title = 'Test Working Group';
        $this->workingGroup->post_content = 'This is a test working group.';
        $this->workingGroup->post_status = 'publish';
        $this->workingGroup->comment_status = 'closed';
        $this->workingGroup->ping_status = 'closed';
        $this->workingGroup->post_name = 'fake-working-group';
        $this->workingGroup->post_type = 'working-group';
        $this->workingGroup->filter = 'raw'; // important!

        // Need to clear post meta
        while (count(get_post_meta($this->workingGroup->ID, 'chairs')) > 0) {
            delete_post_meta($this->workingGroup->ID, 'chairs');
        }
        while (count(get_post_meta($this->workingGroup->ID, 'members')) > 0) {
            delete_post_meta($this->workingGroup->ID, 'members');
        }
        while (count(get_post_meta($this->workingGroup->ID, 'resources')) > 0) {
            delete_post_meta($this->workingGroup->ID, 'resources');
        }
        while (count(get_post_meta($this->workingGroup->ID, 'join_request')) > 0) {
            delete_post_meta($this->workingGroup->ID, 'join_request');
        }

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
        add_metadata('user', $this->user_1->ID, 'working-group', $this->workingGroup->ID);

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
       
        add_post_meta($this->workingGroup->ID, 'chairs', $this->chair->to_array());     // Adds the Chair to the Working Group

        add_post_meta($this->workingGroup->ID, 'members', $this->user_1);     // Adds the Chair to the Working Group
        add_user_meta($this->user_1->ID, 'working-group', $this->workingGroup->ID);

        add_post_meta($this->workingGroup->ID, 'resources', $this->wg_resource->ID); // Adds the Resource to the Working Group
    
    }

    public function test_get_user_data() {
        include_once 'listserv.php';
        $user_1_data = get_user_data($this->user_1);
        $this->assertEquals("Test User1", $user_1_data['user_name']);
        $this->assertEquals("test1@email.com", $user_1_data['user_email']);
        $this->assertEquals("He/Him/His", $user_1_data['user_pronouns']);
        $this->assertEquals("US", $user_1_data['user_country']);
        $this->assertEquals("NCSU", $user_1_data['user_affiliation']);
        $this->assertEquals("Earthquake Engineering, Seismology", $user_1_data['user_expertise']);
        $this->assertEquals("Site Response Analysis", $user_1_data['user_topics']);
        $this->assertEquals("None", $user_1_data['user_working_groups']);
    
        $user_2_data = get_user_data($this->user_2);
        $this->assertEquals("Test User2", $user_2_data['user_name']);
        $this->assertEquals("test2@email.com", $user_2_data['user_email']);
        $this->assertEquals("She/Her/Hers", $user_2_data['user_pronouns']);
        $this->assertEquals("AR", $user_2_data['user_country']);
        $this->assertEquals("GeoQuake", $user_2_data['user_affiliation']);
        $this->assertEquals("Geotechnical Engineering, Seismology", $user_2_data['user_expertise']);
        $this->assertEquals("Seismic Slope Stability", $user_2_data['user_topics']);
        $this->assertEquals($this->workingGroup1->post_title . ", " . $this->workingGroup2->post_title, $user_2_data['user_working_groups']);

    }

    public function test_get_expertise() {
        include_once 'listserv.php';
        $users = array('0' => $this->user_1, '1' => $this->user_2);
        $this->assertEquals(array('Earthquake Engineering', 'Geotechnical Engineering', 'Seismology'), get_expertise($users));
    }

    // public function test_get_emails_for_groups() {
    //     include_once 'listserv.php';
    //     $user_1_data = get_user_data($this->user_1);
    //     $this->assertEquals(array($user_1_data['user_email']), get_emails_for_groups($this->workingGroup->ID));
    // }

    public function test_get_emails_for_wg() {
        include_once 'listserv.php';
        $users = array('0' => $this->user_1, '1' => $this->user_2);
        $user_2_data = get_user_data($this->user_2);
        $this->assertEquals(get_emails_for_wg($users, $this->workingGroup2->post_title), array($user_2_data['user_email']));
    }

    public function test_get_emails_for_expertise() {
        include_once 'listserv.php';
        $users = array($this->user_1, $this->user_2);
        $user_1_data = get_user_data($this->user_1);
        $user_2_data = get_user_data($this->user_2);
        $expertise = 'Seismology';
        $this->assertEquals(array($user_1_data['user_email'], $user_2_data['user_email']), get_emails_for_expertise($users, $expertise));
    }

    public function test_get_all_emails() {
        include_once 'listserv.php';
        $users = array($this->user_1, $this->user_2);
        $user_1_data = get_user_data($this->user_1);
        $user_2_data = get_user_data($this->user_2);
        $this->assertEquals(array($user_1_data['user_email'], $user_2_data['user_email']), get_all_emails($users));
    }
}