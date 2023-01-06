<?php
require dirname(__FILE__) . "/../../../e2scala-working-groups.php";

class Test_E2scala_Working_Groups extends WP_UnitTestCase {

    private $e2scalaWorkingGroup;
    private $chair;
    private $member;
    private $nonmember;
    private $workingGroup;
    private $wg_resource;

    protected function setUp(): void
    {
        // Creates Working Group
        $this->e2scalaWorkingGroup = new E2SCALAWorkingGroup();

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

        add_post_meta($this->workingGroup->ID, 'chairs', $this->chair->to_array());     // Adds the Chair to the Working Group

        add_post_meta($this->workingGroup->ID, 'members', $this->member->to_array());     // Adds the Chair to the Working Group
        add_user_meta($this->member->ID, 'working-group', $this->workingGroup->ID);

        add_post_meta($this->workingGroup->ID, 'resources', $this->wg_resource->ID); // Adds the Resource to the Working Group
    }

    public function test_working_groups_is_member() {
        $this->assertFalse($this->e2scalaWorkingGroup->isMember($this->workingGroup->ID, $this->nonmember->ID));
        $this->assertTrue($this->e2scalaWorkingGroup->isMember($this->workingGroup->ID, $this->member->ID));
        $this->assertFalse($this->e2scalaWorkingGroup->isMember($this->workingGroup->ID, $this->chair->ID));
    }

    public function test_working_groups_is_chair() {
        $this->assertFalse($this->e2scalaWorkingGroup->isChair($this->workingGroup->ID, $this->nonmember->ID));
        $this->assertFalse($this->e2scalaWorkingGroup->isChair($this->workingGroup->ID, $this->member->ID));
        $this->assertTrue($this->e2scalaWorkingGroup->isChair($this->workingGroup->ID, $this->chair->ID));
    }

    public function test_working_groups_get_members() {
        $this->assertEquals(1, count($this->e2scalaWorkingGroup->getMembers($this->workingGroup->ID)));
        $this->assertEquals($this->member->to_array(), $this->e2scalaWorkingGroup->getMembers($this->workingGroup->ID)[0]);
    }

    public function test_working_groups_get_chairs() {
        $this->assertEquals(1, count($this->e2scalaWorkingGroup->getChairs($this->workingGroup->ID)));
        $this->assertEquals($this->chair->to_array(), $this->e2scalaWorkingGroup->getChairs($this->workingGroup->ID)[0]);
    }

    public function test_working_groups_get_resources() {
        $this->assertEquals(1, count($this->e2scalaWorkingGroup->getWGResources($this->workingGroup->ID)));
        $this->assertEquals($this->wg_resource->ID, $this->e2scalaWorkingGroup->getWGResources($this->workingGroup->ID)[0]);
    }

    public function test_working_group_join_bad_request() {
        $two_hundred_fifty_char_answer = "a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a ";
        $two_hundred_fifty_one_char_answer = "a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a";
        $request = array($this->nonmember->ID, $this->workingGroup->ID, array($two_hundred_fifty_char_answer, $two_hundred_fifty_char_answer, $two_hundred_fifty_one_char_answer));
        $this->assertFalse($this->e2scalaWorkingGroup->request_to_join_wg($request)[0]);
        $this->assertEquals(0, count(get_post_meta($this->workingGroup->ID, 'join_request')));
    }

    public function test_working_group_join_request() {
        $two_hundred_fifty_char_answer = "a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a a ";
        $request = array($this->nonmember->ID, $this->workingGroup->ID, array($two_hundred_fifty_char_answer, $two_hundred_fifty_char_answer, $two_hundred_fifty_char_answer));
        $this->assertTrue($this->e2scalaWorkingGroup->request_to_join_wg($request)[0]);
        $this->assertEquals(1, count(get_post_meta($this->workingGroup->ID, 'join_request')));
        $this->assertEquals($this->nonmember->ID, get_post_meta($this->workingGroup->ID, 'join_request')[0][0]);
    }

    public function test_working_group_accept_request() {
        // Create the Working Group Join Request
        $answer = "Answer";
        $request = array($this->nonmember->ID, $this->workingGroup->ID, array($answer, $answer, $answer));
        $response = $this->e2scalaWorkingGroup->request_to_join_wg($request);
        $valid = $response[0];
        $mid = $response[2];
        $this->assertTrue($valid);
        $this->assertEquals(1, count(get_post_meta($this->workingGroup->ID, 'join_request')));
        $this->assertEquals($this->nonmember->ID, get_post_meta($this->workingGroup->ID, 'join_request')[0][0]);

        // Accept the request as the chair.
        wp_set_current_user($this->chair->ID);
        $this->e2scalaWorkingGroup->process_wg_request($this->workingGroup->ID, $this->nonmember->ID, $mid, true);
        $this->assertEquals(2, count($this->e2scalaWorkingGroup->getMembers($this->workingGroup->ID)));
        $this->assertEquals($this->member->to_array(), $this->e2scalaWorkingGroup->getMembers($this->workingGroup->ID)[0]);
        $this->assertEquals($this->nonmember->ID, $this->e2scalaWorkingGroup->getMembers($this->workingGroup->ID)[1]);
    }

    public function test_working_group_reject_request() {
        // Create the Working Group Join Request
        $answer = "Answer";
        $request = array($this->nonmember->ID, $this->workingGroup->ID, array($answer, $answer, $answer));
        $response = $this->e2scalaWorkingGroup->request_to_join_wg($request);
        $valid = $response[0];
        $mid = $response[2];
        $this->assertTrue($valid);
        $this->assertEquals(1, count(get_post_meta($this->workingGroup->ID, 'join_request')));
        $this->assertEquals($this->nonmember->ID, get_post_meta($this->workingGroup->ID, 'join_request')[0][0]);

        // Reject the request as the chair.
        wp_set_current_user($this->chair->ID);
        $this->e2scalaWorkingGroup->process_wg_request($this->workingGroup->ID, $this->nonmember->ID, $mid, false);
        $this->assertEquals(1, count($this->e2scalaWorkingGroup->getMembers($this->workingGroup->ID)));
        $this->assertEquals($this->member->to_array(), $this->e2scalaWorkingGroup->getMembers($this->workingGroup->ID)[0]);
    }
 
}