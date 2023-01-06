<?php

 /**
  * Plugin Name: E2SCALA Working Groups
  * Description: Allows users to create, manage, and view working groups and the resources associated with the working groups.
  * Version: 1.0.0
  * Author: Chris Tong
  * License: GPLv2
  */

defined('ABSPATH') or die;

class E2SCALAWorkingGroup
{
    function __construct() 
    {
        add_shortcode('e2scala_wg_members', array($this, 'working_groups_members'));
        add_shortcode('join_working_group', array($this, 'join_working_group'));
        add_shortcode('process_wg_request', array($this, 'process_wg_request_display'));
        add_shortcode('e2scala-working-groups', array($this, 'display_working_groups'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue'));
    }

    function register_admin_scripts() 
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueue'));
    }

    function activate() 
    {
        // You would use this to provide a function to set up your plugin — for example, creating some default settings in the options table.
        flush_rewrite_rules();
    }

    function deactivate() 
    {
        // You would use this to provide a function that clears any temporary data stored by your plugin.
    }
    function enqueue() 
    {
        // enqueue all our scripts
        wp_enqueue_style('e2scala_style', plugins_url('/assets/style.css', '__FILE__'));
        wp_enqueue_script('e2scala_script', plugins_url('/assets/script.css', '__FILE__'));
    }

    function getMembers($wg_id)
    {
        return get_post_meta($wg_id, 'members');
    }

    /**
     * Returns true if the user is a member of the working group.
     */
    function isMember($wg_id, $user_id): bool
    {
        $is_member = false;
        $members = $this->getMembers($wg_id);
        foreach ($members as $member) {
            if ($member['ID'] == $user_id) {
                $is_member = true;
            }
        }

        return $is_member;
    }

    function getChairs($wg_id)
    {
        return get_post_meta($wg_id, 'chairs');
    }
    
    /**
     * Returns true if the user is a chair of the working group.
     */
    function isChair($wg_id, $user_id): bool
    {
        $is_chair = false;
        $chairs = $this->getChairs($wg_id);
        foreach ($chairs as $chair) {
            if ($chair['ID'] == $user_id) {
                $is_chair = true;
            }
        }
        return $is_chair;
    }

    function getWGResources($wg_id)
    {
        return get_post_meta($wg_id, 'resources');
    }

    function working_groups_members($atts = [], $content = null, $tag = '') {
        ob_start();
        // Validate that:
        // - The url is correct
        if (!str_starts_with(get_permalink(), site_url('/working-group/')) && !str_starts_with(get_permalink(), site_url('/grupo-de-trabajo/'))) {
            return ob_get_clean();
        }
        // normalize attribute keys, lowercase
        $atts = array_change_key_case( (array) $atts, CASE_LOWER );
    
        // override default attributes with user attributes
        $wg_members_atts = shortcode_atts(
            array(
                'wg_id' => '',
                'tt_id' => '',
            ), $atts, $tag
        );
        $user_id = get_current_user_id();
        $wg_id = $wg_members_atts['wg_id'];

        $valid_member = $this->isMember($wg_id, $user_id);
        $is_chair = $this->isChair($wg_id, $user_id);

        if ($valid_member == true || $is_chair == true) {
            echo $this -> generalMemberView($wg_id);
        } else {
            echo $this -> generalNonMemberView($wg_id);
        }

        
        if ($is_chair == true) {
            echo $this -> chairView($wg_id);
        }

        return ob_get_clean();
    }

    function generalNonMemberView($wg_id) {
        ob_start();
        $join_link = "";
        $join_message = "";
        if (get_locale() == 'en_US') {
            $join_link = "/join-working-group";
            $join_message = "Join Working Group";
        } else if (get_locale() == 'es_ES') {
            $join_link = "/unirse-al-grupo-de-trabajo";
            $join_message = "Unirse al Grupo de Trabajo";
        }
        ?>
            <br>
            <form action='<?=$join_link?>' method='post'>
                <input type='hidden' name='wg_id' value='<?=$wg_id?>'>
                <input type='submit' value='<?=$join_message?>' style='width: 15em;'>
            </form>
        <?php
        return ob_get_clean();
    }

    function generalMemberView($wg_id) {
        $wg_resources = $this->getWGResources($wg_id);
        $deliverables = array();
        $agendas = array();
        $events = array();
        foreach ($wg_resources as $resource) {
            if (get_post_meta($resource['ID'], 'wg_resource_category')[0] == "Deliverable") {
                array_push($deliverables, $resource);
            } else if (get_post_meta($resource['ID'], 'wg_resource_category')[0] == "Meeting Agenda") {
                array_push($agendas, $resource);
            } else if (get_post_meta($resource['ID'], 'wg_resource_category')[0] == "Event") {
                array_push($events, $resource);
            }
        }

        $chairs = $this->getChairs($wg_id);

        $chairs_label = "";
        $deliverables_label = "";
        $meeting_agendas_label = "";
        $events_label = "";
        
        if (get_locale() == 'en_US') {
            $chairs_label = "Chairs";
            $deliverables_label = "Deliverables";
            $meeting_agendas_label = "Meeting Agendas";
            $events_label = "Events";
        } else if (get_locale() == 'es_ES') {
            $chairs_label = "Lideres";
            $deliverables_label = "Entregables";
            $meeting_agendas_label = "Agendas de Reuniones";
            $events_label = "Eventos";
        }

        ob_start();
        ?>
        <div>
            <br>
            <br>
            <h2><?=$chairs_label?></h2>
            <?php foreach ($chairs as $chair) {
                ?> <p> <?=esc_html__($chair['display_name'])?> </p> <?php
            } ?>
            </div>
            <br>
            <div class='wp-block-columns'>
                <div class='wp-block-column'>
                    <h2><?=$deliverables_label?></h2>
                    <?php foreach ($deliverables as $deliverable) {
                        ?><p><a href='wg-resources/<?=esc_html__($deliverable['post_name'])?>'><?=esc_html__($deliverable['post_title'])?></a></p><?php
                    } ?>
                </div>

                <div class='wp-block-column'>
                    <h2><?=$meeting_agendas_label?></h2>
                    <?php foreach ($agendas as $agenda) {
                        ?><p><a href='wg-resources/<?=esc_html__($agenda['post_name'])?>'><?=esc_html__($agenda['post_title'])?></a></p><?php
                    } ?>
                </div>

                <div class='wp-block-column'>
                    <h2><?=$events_label?></h2>
                    <?php foreach ($events as $event) {
                        ?><p><a href='wg-resources/<?=esc_html__($event['post_name'])?>'><?=esc_html__($event['post_title'])?></a></p><?php
                    } ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    function chairView($wg_id) {
        $members = $this->getMembers($wg_id);

        $members_label = "";
        $membership_request_label = "";
        $process_url = "";

        if (get_locale() == 'en_US') {
            $members_label = "Members";
            $membership_request_label = "Membership Requests";
            $process_url = "/process-wg-request";
        } else if (get_locale() == 'es_ES') {
            $members_label = "Miembros";
            $membership_request_label = "Solicitudes de Membresía";
            $process_url = "/procesar-solicitud-de-grupo-de-trabajo";
        }

        ob_start();
        // Members and Membership requests
        ?>
        <div class='wp-block-columns'>
            <div class='wp-block-column'>
            <h2><?=$members_label?></h2>
            <?php
            foreach ($members as $member) {
                ?>
                <p><?=esc_html__($member['display_name'])?></p>
                <?php
            }
            $wg_membership_requests = $this->get_complete_meta($wg_id, 'join_request');
            ?>
            </div>
            <div class='wp-block-column'>
                <h2><?=$membership_request_label?></h2>
                <?php
                foreach ($wg_membership_requests as $request) {
                    $request_mid = $request->meta_id;
                    $join_request = get_metadata_by_mid('post', $request_mid);
                    $user_name = get_userdata($join_request->meta_value[0])->get('display_name');
                    ?>
                    <form action='<?=$process_url?>' method='post'>
                        <input type='hidden' name='request_id' value='<?=esc_html__($request_mid)?>'>
                        <input type='submit' value='<?=esc_html__($user_name)?>' style='width: 15em;'>
                    </form>
                    <?php
                }
                ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    function request_to_join_wg($request) {
        $valid = false;
        $user_id = $request[0];
        $wg_id = $request[1];
        $questions = $request[2];
        $mid = null;
        // TODO: INPUT VALIDATION
        $content = array($user_id, array($questions[0], $questions[1], $questions[2]));
        $q1_err = str_word_count($questions[0]) > 250;
        $q2_err = str_word_count($questions[1]) > 250;
        $q3_err = str_word_count($questions[2]) > 250;
        $errs = array($q1_err, $q2_err, $q3_err);
        if (!$q1_err && !$q2_err && !$q3_err) {
            $valid = true;
            $mid = add_post_meta($wg_id, 'join_request', $content);
        }
        return array($valid, $questions, $mid, $errs);
    }

    function join_working_group() {
        ob_start();
        // Validate that:
        // - The url is correct
        if (!str_starts_with(get_permalink(), site_url('/join-working-group/')) && !str_starts_with(get_permalink(), site_url('/unirse-al-grupo-de-trabajo/'))) {
            return ob_get_clean();
        }

        $wg_id = $_POST['wg_id'];
        $user_id = get_current_user_id();
        
        $post   = get_post( $wg_id )->to_array();
        $wg_title = $post['post_title'];
        $wg_url = "/working-group/" . $post['post_name'];
        $q1_val = "";
        $q2_val = "";
        $q3_val = "";
        $q1_err = false;
        $q2_err = false;
        $q3_err = false;

        $success_message = "";
        $sign_in_message = "";
        $existing_member_message = "";
        $existing_request_message = "";
        $question_1 = "";
        $question_2 = "";
        $question_3 = "";
        $error_message = "";
        $submit_message = "";

        if (get_locale() == "en_US") {
            $success_message = "You have successfully requested to join this working group! Click <a href='$wg_url' style='color:#1e81b0; font-size: 1.17em;'>here</a> to return to the working group.";
            $sign_in_message = "You must be signed in to join this working group. Click <a href='/registration' style='color:#1e81b0; font-size: 1.17em;'>here</a> to create an account.";
            $existing_member_message = "You are already a member of this working group. Click <a href='$wg_url' style='color:#1e81b0; font-size: 1.17em;'>here</a> to go back to the working group's page.";
            $existing_request_message = "You have already requested to join this working group. Click <a href='$wg_url' style='color:#1e81b0; font-size: 1.17em;'>here</a> to go back to the working group's page.";
            $question_1 = "Why are you interested in joining this working group? <i>(250 word limit)</i>";
            $question_2 = "What are your expectations? <i>(250 word limit)</i>";
            $question_3 = "How do you think you can contribute to the working group? <i>(250 word limit)</i>";
            $error_message = "Must be 250 words or less.";
            $submit_message = "Join";
        } else if (get_locale() == "es_ES") {
            $success_message = "¡Ha solicitado con éxito unirse a este grupo de trabajo! Haga clic <a href='$wg_url' style='color:#1e81b0; font-size: 1.17em;'>aquí</a> para volver al grupo de trabajo.";
            $sign_in_message = "Debe iniciar sesión para unirse a este grupo de trabajo. Haga clic <a href='/registration' style='color:#1e81b0; font-size: 1.17em;'>aquí</a> para crear una cuenta.";
            $existing_member_message = "Ya eres miembro de este grupo de trabajo. Haga clic <a href='$wg_url' style='color:#1e81b0; font-size: 1.17em;'>aquí</a> para volver al grupo de trabajo.";
            $existing_request_message = "Ya ha solicitado unirse a este grupo de trabajo. Haga clic <a href='$wg_url' style='color:#1e81b0; font-size: 1.17em;'>aquí</a> para volver al grupo de trabajo.";
            $question_1 = "¿Por qué te interesa formar parte de este grupo de trabajo? <i>(Límite de 250 palabras)</i>";
            $question_2 = "¿Cuales son tus expectativas? <i>(Límite de 250 palabras)</i>";
            $question_3 = "¿Cómo crees que puedes contribuir al grupo de trabajo? <i>(Límite de 250 palabras)</i>";
            $error_message = "Debe tener 250 palabras o menos.";
            $submit_message = "Entrar";
        }

        if ($_POST['question_1'] && $_POST['question_2'] && $_POST['question_3']) {
            $request = array($user_id, $wg_id, array($_POST['question_1'], $_POST['question_2'], $_POST['question_3']));
            $response = $this->request_to_join_wg($request);

            if ($response[0]) {
                ?>
                <div style='margin: auto; width: 60vw;'>
                    <br>
                    <br>
                    <h3><?=$success_message?></h3>
                </div>
                <?php
                return ob_get_clean();
            } else {
                $q1_err = $response[3][0];
                $q2_err = $response[3][1];
                $q3_err = $response[3][2];

                $q1_val = $response[1][0];
                $q2_val = $response[1][1];
                $q3_val = $response[1][2];
            }
        }
        ?>
        <div style='margin: auto; width: 60vw;'>
            <br>
            <br>
            <h1>Join <?=$wg_title?></h1>
            <?php
            // If the user is not signed in
            if ($user_id == 0) {
                ?>
                <br>
                <br>
                <h3><?=$sign_in_message?></h3>
                <?php
                return ob_get_clean();
            }

            // Make sure user is not already in the group
            if ($this->isMember($wg_id, $user_id) || $this->isChair($wg_id, $user_id)) {
                // Say you are already part of the working group
                ?>
                <br>
                <br>
                <h3><?=$existing_member_message?></h3>
                <?php
                return ob_get_clean();
            }

            // If they already requested to join,
            $wg_requests = get_post_meta($wg_id, 'join_request');
            $already_requested = false;
            foreach ($wg_requests as $request) {
                if ($request[0] == $user_id) {
                    $already_requested = true;
                }
            }
            if ($already_requested) {
                // Say you are already part of the working group
                ?>
                <br>
                <br>
                <h3><?=$existing_request_message?></h3>
                <?php
                return ob_get_clean();
            }


            // Main Request Form
            ?>
            <form action='' method='post' id='requestform'>
                <br>
                <label><?=$question_1?></label>
                <br>
                <br>
                <label <?php if (!$q1_err) { ?> hidden<?php } ?> style='color:red;'><i><?=$error_message?></i></label>
                <textarea type='text' name='question_1' rows='5' cols='50'><?=$q1_val?></textarea>
                <br>
                <br>
                <br>

                <label><?=$question_2?></label>
                <br>
                <br>
                <label <?php if (!$q2_err) { ?> hidden<?php } ?> style='color:red;'><i><?=$error_message?></i></label>
                <textarea type='text' name='question_2' rows='5' cols='50'><?=$q2_val?></textarea>
                <br>
                <br>
                <br>

                <label><?=$question_3?></label>
                <br/>
                <br/>
                <label <?php if (!$q3_err) { ?> hidden<?php } ?> style='color:red;'><i><?=$error_message?></i></label>
                <textarea type='text' name='question_3' rows='5' cols='50'><?=$q3_val?></textarea>
                <br/>
                <br/>
                <br/>
                <input type='hidden' name='wg_id' value='<?=$wg_id?>' />
                <input type='submit' value='<?=$submit_message?>'>
            </form>
        </div>

        <?php
        return ob_get_clean();
    }

    function process_wg_request($wg_id, $user_id, $join_request, $accepted) {
        if ($accepted) {
            add_post_meta($wg_id, 'members', $user_id);
            add_user_meta($user_id, 'working-group', $wg_id);
        }
        return delete_post_meta($wg_id, 'join_request', $join_request);
    }

    function process_wg_request_display() {
        ob_start();
        // Validate that:
        // - The url is correct
        if (!str_starts_with(get_permalink(), site_url('/process-wg-request/')) && !str_starts_with(get_permalink(), site_url('/procesar-solicitud-de-grupo-de-trabajo/'))) {
            return ob_get_clean();
        }
        $request_id = intval($_POST['request_id']);
        $wg_id = get_metadata_by_mid('post', $request_id)->post_id;
        $join_request = get_metadata_by_mid('post', $request_id)->meta_value;

        $join_user_id = $join_request[0];
        $questions = $join_request[1];

        $post = get_post( $wg_id )->to_array();
        $wg_url = "/working-group/" . $post['post_name'];

        $decision = $_POST['decision'] . "ed";

        $not_chair_message = "";
        $processed_message = "";
        $question_1 = "";
        $question_2 = "";
        $question_3 = "";
        $accept_message = "";
        $reject_message = "";

        if (get_locale() == "en_US") {
            $not_chair_message = "You are not the chair of this working group. Click <a href='$wg_url' style='color:#1e81b0; font-size: 1.17em;'>here</a> to return to the working group.";
            $processed_message = "The user's working group join request has been $decision. Click <a href='$wg_url' style='color:#1e81b0; font-size: 1.17em;'>here</a> to return to the working group.";
            $question_1 = "Why are you interested in joining this working group?";
            $question_2 = "What are your expectations?";
            $question_3 = "How do you think you can contribute to the working group?";
            $accept_message = "Accept Member";
            $reject_message = "Reject Member";
        } else if (get_locale() == "es_ES") {
            $not_chair_message = "Usted no es el presidente de este grupo de trabajo. Haga clic <a href='$wg_url' style='color:#1e81b0; font-size: 1.17em;'>aquí</a> para volver al grupo de trabajo.";
            if ($decision == "accepted") {
                $processed_message = "Se ha aceptado la solicitud de unión al grupo de trabajo del usuario. Haga clic <a href='$wg_url' style='color:#1e81b0; font-size: 1.17em;'>aquí</a> para volver al grupo de trabajo.";
            } else if ($decision == "rejected") {
                $processed_message = "La solicitud de unirse al grupo de trabajo del usuario ha sido rechazada. Haga clic <a href='$wg_url' style='color:#1e81b0; font-size: 1.17em;'>aquí</a> para volver al grupo de trabajo.";
            }
            $question_1 = "¿Por qué te interesa formar parte de este grupo de trabajo?";
            $question_2 = "¿Cuales son tus expectativas?";
            $question_3 = "¿Cómo crees que puedes contribuir al grupo de trabajo?";
            $accept_message = "Aceptar Miembro";
            $reject_message = "Rechazar Miembro";
        }


        // - The current user is the chair of the working group that's requested to be joined
        $valid_chair = $this->isChair($wg_id, get_current_user_id());
        if ($valid_chair == false) {
            ?>
            <br>
            <br>
            <h3><?=$not_chair_message?></h3>
            <?php
            return ob_get_clean();
        }

        if ($_POST['decision']) {
            if ($_POST['decision'] == 'accept') {
                $this->process_wg_request($wg_id, $join_user_id, $join_request, true);
            } else if ($_POST['decision'] == 'reject') {
                $this->process_wg_request($wg_id, $join_user_id, $join_request, false);
            }
            ?>
            <br>
            <br>
            <h3><?=$processed_message?></h3>
            <?php
            return ob_get_clean();
        }
        

        $user = get_userdata($join_user_id);
        $first_name = get_user_meta($join_user_id, 'first_name', true);
        $last_name = get_user_meta($join_user_id, 'last_name', true);

        ?>
        <div style='margin: auto; width: 60vw;'>
            <h2><?=$first_name?> <?=$last_name?></h2>
            <strong><?=$user->get('user_email')?></strong>

            <br>
            <br>
            <br>

            <h3><?=$question_1?></h3>
            <p><?=$questions[0]?></p>
            
            <br>

            <h3><?=$question_2?></h3>
            <p><?=$questions[1]?></p>

            <br>

            <h3><?=$question_3?></h3>
            <p><?=$questions[2]?></p>
            
            <br>

            <form action='' method='post'>
                <input type='hidden' name='request_id' value='<?=$request_id?>'>
                <input type='hidden' name='decision' value='accept'>
                <input type='submit' value='<?=$accept_message?>' style='width: 12em;'>
            </form><br>

            <form action='' method='post'>
                <input type='hidden' name='request_id' value='<?=$request_id?>'>
                <input type='hidden' name='decision' value='reject'>
                <input type='submit' value='<?=$reject_message?>' style='width: 12em;'>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    function display_working_groups() {
        ob_start();

        // Validate that the url is correct
        if (!str_starts_with(get_permalink(), site_url('/working-groups/')) && !str_starts_with(get_permalink(), site_url('/grupos-de-trabajo/'))) {
            return ob_get_clean();
        }

        // Need to get the user's working groups
        $user_working_groups = get_user_meta(get_current_user_id(), 'working-group');
        if ($user_working_groups == "") {
            $user_working_groups = [];
        }

        // Need to get all working groups
        $all_working_groups = get_posts(['post_type' => 'working_group']);

        $my_working_groups_label = "";
        $all_working_groups_label = "";
        $chair_label = "";
        $created_on_label = "";
        $join_working_group_label = "";
        $join_working_group_url = "";
        $browse_message = "";

        if (get_locale() == "en_US") {
            $my_working_groups_label = "My Working Groups";
            $all_working_groups_label = "All Working Groups";
            $chair_label = "Chair";
            $created_on_label = "Created on";
            $join_working_group_label = "Join Working Group";
            $join_working_group_url = "/join-working-group";
            $browse_message = "Browse all Working Groups to join one!";
        } else if (get_locale() == "es_ES") {
            $my_working_groups_label = "Mis Grupos de Trabajo";
            $all_working_groups_label = "Todos los Grupos de Trabajo";
            $chair_label = "Líder";
            $created_on_label = "Creado en";
            $join_working_group_label = "Unirse al Grupo de Trabajo";
            $join_working_group_url = "/unirse-al-grupo-de-trabajo";
            $browse_message = "¡Explore todos los grupos de trabajo para unirse a uno!";
        }

        ?>
        <div class="wp-block-columns">
            <div class='wp-block-column' style='float: left; width: 50%;'>
                <h2><?=$my_working_groups_label?></h2>
                <?php
                // If the user is a chair of a working group, add it to their working groups.
                foreach ($all_working_groups as $working_group) {
                    $is_chair = false;
                    $chairs = get_post_meta($working_group->to_array()['ID'], 'chairs');
                    foreach ($chairs as $chair) {
                        if ($chair['ID'] == get_current_user_id()) {
                            $is_chair = true;
                        }
                    }
                    if ($is_chair) {
                        array_push($user_working_groups, $working_group->to_array()['ID']);
                    }
                }

                // Groups that the user is a member of
                if ($user_working_groups != "") {
                    foreach ($user_working_groups as $working_group) {
                        $wg = get_post($working_group)->to_array();
                        $author = get_user_by('ID', $wg['post_author'])->get('display_name');
                        $date = date_format(date_create($wg['post_date']), "F j, Y");
                        ?>
                        <div style='justify-content: start;'>
                            <a href="<?=$wg['post_name']?>">
                                <div class="working-group">
                                    <h3><?=$wg['post_title']?></h3>
                                    <h5><?=$wg['post_content']?></h5>
                                    <h6><?=$chair_label?>: <?=$author?> · <?=$created_on_label?> <?=$date?></h6>
                                </div>
                            </a>
                        </div>
                        <hr>
                        <?php
                    }
                } else {
                    ?>
                    <div>
                        <h4><?=$browse_message?></h4>
                    </div>
                    <?php
                }
                ?>
            </div>
            <div class='wp-block-column' style='float: right; width: 50%;'>
                <h2><?=$all_working_groups_label?></h2>
                <?php
                foreach ($all_working_groups as $working_group) {
                    $wg = $working_group->to_array();
                    $author = get_user_by('ID', $wg['post_author'])->get('display_name');
                    $date = date_format(date_create($wg['post_date']), "F j, Y");
                    ?>
                    <div style='justify-content: start;'>
                        <a href="<?=$wg['post_name']?>">
                            <div class="working-group">
                            <h3><?=$wg['post_title']?></h3>
                                    <h5><?=$wg['post_content']?></h5>
                                    <h6><?=$chair_label?>: <?=$author?> · <?=$created_on_label?> <?=$date?></h6>
                            </div>
                        </a>
                        <?php
                        // if the user is a chair or member of working group, dont show the join button
                        if (!in_array($wg['ID'], $user_working_groups)) {
                            ?>
                            <form action='<?=$join_working_group_url?>' method='post'>
                                <input type='hidden' name='wg_id' value='<?=$wg['ID']?>'>
                                <input type='submit' value='<?=$join_working_group_label?>' style='width: 15em;'>
                            </form>
                            <?php
                        }
                        ?>
                    </div>
                    <hr>
                    <?php
                }
                ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    function get_complete_meta( $post_id, $meta_key ) {
        global $wpdb;
        $mid = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s", $post_id, $meta_key) );
        if( $mid != '' )
            return $mid;

        return false;
    }

    function get_translated_post_ids($tt_id) {
        global $wpdb;
        $tt_details_results = $wpdb->get_results("SELECT * FROM $wpdb->term_taxonomy WHERE term_taxonomy_id = ($tt_id)");
        $tt_details = $tt_details_results[0];
        return unserialize($tt_details->description);
    }

    static function help() {
        return "HELP";
    }
}



if (class_exists('E2SCALAWorkingGroup')) {
    $e2scalaWorkingGroup = new E2SCALAWorkingGroup();
    $e2scalaWorkingGroup->register_admin_scripts();
}

// function help() {
//     echo "HELP";
// }

// activation
register_activation_hook(__FILE__, array($e2scalaWorkingGroup, 'activate'));

// deactivation
register_deactivation_hook(__FILE__, array($e2scalaWorkingGroup, 'deactivate'));
