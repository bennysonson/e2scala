<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @since             1.0.0
 * @package           listserv
 *
 * @wordpress-plugin
 * Plugin Name:       Listserv Plugin
 * Description:       Plugin which allows admin to send emails to subcribers of the listserv
 * Version:           1.0.0
 * Author:            Sean McKone, Alex Yang
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       plugin-name
 * Domain Path:       /languages
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGIN_NAME_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin-name-activator.php';
	Plugin_Name_Activator::activate();
}

add_action('wp_register_style', 'activate_css');
function activate_css() {
	wp_register_style( 'namespace', 'admin/css/plugin-name-admin.css' );
	wp_enqueue_style( 'namespace' );
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin-name-deactivator.php';
	Plugin_Name_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_plugin_name' );
register_deactivation_hook( __FILE__, 'deactivate_plugin_name' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-plugin-name.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_plugin_name() {

	$plugin = new Plugin_Name();
	$plugin->run();

}

run_plugin_name();

/**
 * 
 * Newly added irrevelant code to test the concept
 * 
 */

add_action('admin_menu', 'test_plugin_setup_menu');
function test_plugin_setup_menu(){
    add_menu_page( 'Admin Page', 'Listserv Plugin', 'manage_options', 'test-plugin', 'test_init' );
}

function test_init(){

	$users = get_users();

	// debug statements, don't remove
	// foreach ($users as $user) {
    //     $user_data = get_user_data($user);
	// 	echo '<pre>'; print_r($user_data); echo '</pre>';
	// }
	
	$expertises = get_expertise($users);
	$groups = get_groups();

	?>
		<h1>E<sup>2</sup>SCALA Mailing List</h1>
		<hr>
		<h3>How to use the E<sup>2</sup>SCALA Mailing List</h3>
		<ol>
			<li>Ensure you are signed into the E<sup>2</sup>SCALA Generic Email Account<br>(You may need to create a separate profile in Chrome)</li>
			<li>Check the boxes of the groups you would like to email</li>
			<li>Press "Add Recipients" and wait for the page to reload</li>
			<li>Check the "Added Recipients" box to ensure all releveant emails were added</li>
			<li>Click the "Send Email" button". You will be redirected to a Gmail tab</li>
		</ol>
		<hr>



		<form method="post">
			
			<h3>Expertise</h3>

			<?php
				foreach($expertises as $item){
				
					$str = '<tr>

					<td><input type="checkbox" name="formExpertises[]" value="'.$item.'"></td>
					
					<td>'.$item.'</td></tr>';

					echo $str;
					echo '<br>';
					
				}

				if(isset($_POST["formExpertises[]"])) echo('<p>'.$_POST["formExpertises[]"].'</p>');
			?>

			<h3>Working Groups</h3>

			<?php

				foreach($groups as $key=>$value){
				
					$str = '<tr>

					<td><input type="checkbox" name="formGroups[]" value="'.$value.'"></td>
					
					<td>'.$key.'</td></tr>';

					echo $str;
					echo '<br>';
					
				}

				if(isset($_POST["formGroups[]"])) echo('<p>'.$_POST["formGroups[]"].'</p>');

			?>

			<h3>All</h3>

			<div>
			<input type="checkbox" id="all" name="all">
			<label for="all">All</label>
			</div>
			<br><br>
			<input type="submit" value="Add Recipients">
			<hr>
		</form>


		<?php echo $_POST["all"]; ?><br>

		<?php

		$aDoor = $_POST['formExpertises'];
		$selectedgroups = $_POST['formGroups'];

		$gmail_link_template = "https://mail.google.com/mail/?view=cm&source=mailto&bcc=";

		$gmail_link = $gmail_link_template;

		$emails_to_add = array();

		for($i=0; $i < count($aDoor); $i++){

			$emails = get_emails_for_expertise($users, $aDoor[$i]);

			foreach ($emails as $email) {
				array_push($emails_to_add, $email);
			}	
		}

		for($i=0; $i < count($selectedgroups); $i++){
			$emails = get_emails_for_groups($selectedgroups[$i]);
			foreach ($emails as $email) {
				array_push($emails_to_add, $email);
			}	
		}

		// if "All" checkbox marked, add all emails in database
		// to the link
		if($_POST["all"]) {
			$emails = get_all_emails($users);
			foreach ($emails as $email) {
				array_push($emails_to_add, $email);
			}
		}

		// Remove duplicate emails
		$emails_to_add = array_unique($emails_to_add);

		$email_output_string = "";

		// Add all emails to the link string
		foreach ($emails_to_add as $email) {
			$gmail_link.=$email;
			$gmail_link.=",";

			$email_output_string.= $email;
			$email_output_string.="\n";
		}
		?>
		<h3>Added Recipients</h3>
		<textarea id="addedemails" disabled rows="6" style="resize: none;"><?php echo $email_output_string ?></textarea>
		<br><br>
		<a href="<?=$gmail_link?>" target="_blank" rel="noopener noreferrer" style="font-size: 20px; text-decoration: none;">Send Email</a>
	<?php

	//echo(gettype(get_user_data($users[2])["user_expertise"]));

	//echo($gmail_link);

}

function get_groups()
{
	$all_working_groups = get_posts(['post_type' => 'working_group']);

	// debug statement to check if working groups are being retrieved
	// foreach ($all_working_groups as $wg) {
	// 	print_r($wg);
	// 	echo('<br>');
	// }
	$groups_array = array();

	foreach ($all_working_groups as $group_name) {
		$wg = $group_name->to_array();

		$group_name = $wg["post_title"];
		$group_id = $wg["ID"];
		$groups_array[$group_name] = $group_id;
	}

	ksort($groups_array);
	return $groups_array;
}

function get_emails_for_groups($group_id)
{

	//Get data for working groups display
	$post_meta = get_post_meta($group_id);

	//Number of members and member names
	$membersId = $post_meta['members'];
	$membersArray = [];
	$numbMembers = count($membersId);
	foreach ($membersId as $memberId) {
		$memberEmail = get_user_by('ID', $memberId)->get('user_email');
		array_push($membersArray, $memberEmail);
	}
	return $membersArray;
}

function get_user_data($user)
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

function get_expertise($users) {

	$expertises = array();

	foreach ($users as $user) {
		$user_data = get_user_data($user);
		$expertises_array = preg_split("/\,/", $user_data["user_expertise"], -1, PREG_SPLIT_NO_EMPTY);
		$filtered_expertises_array = array_filter($expertises_array);
		foreach ($filtered_expertises_array as $expertise) {
			$expertise = trim($expertise);
			// debug statements: 
			// echo '<pre>'; print_r($expertises); echo '</pre>';
			// echo "<h2>" .$expertise. "<h2>";
			if (!in_array($expertise, $expertises)) {
				array_push($expertises, $expertise);
			}
		}
	}

	sort($expertises);	
	return $expertises;
}

// Return an array of user emails that have the matching working group
// $users: array of users
// $expertise: string name of expertise
// return: array of user emails
function get_emails_for_wg($users, $working_group) {
	$matching_emails = array();

	foreach ($users as $user) {
		$user_data = get_user_data($user);
		if(str_contains($user_data["user_working_groups"], $working_group)) {
			array_push($matching_emails, $user_data["user_email"]);
		}
	}
	
	return $matching_emails;
}


// Return an array of user emails that have the matching expertise
// $users: array of users
// $expertise: string name of expertise
// return: array of user emails
function get_emails_for_expertise($users, $expertise) {
	$matching_emails = array();
	foreach ($users as $user) {
		$user_data = get_user_data($user);
		// echo( "?" . $user_data["user_expertise"] . "?" . $expertise . "?");
		if(str_contains($user_data["user_expertise"], $expertise)) {
			array_push($matching_emails, $user_data["user_email"]);
		}
	}
	
	return $matching_emails;
}

// Return an array of all user emails
// $users: array of users
// return: array of user emails
function get_all_emails($users) {
	$matching_emails = array();

	foreach ($users as $user) {
		$user_data = get_user_data($user);
		array_push($matching_emails, $user_data["user_email"]);	
	}

	return $matching_emails;
}
