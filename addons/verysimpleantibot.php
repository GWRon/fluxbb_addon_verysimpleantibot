<?php
/**
 * Very Simple AntiBot
 * ===================
 *
 * The addon adds a verification to "Fluxbb" to distinguish between
 * automated requests and humans.
 * The verification is based on user defined questions and answers to
 * make dictionary attacks up to useless.
 *
 * The addon hooks into various spots of the Fluxbb-software.
 * hooks: registration, post, quickpost
 *
 * It is based on the nifty addon "Very Simple AntiBot Registration"
 * but utilizes the new features of "Fluxbb v1.5.8+" which can be found
 * here:
 * http://fluxbb.org/resources/mods/very-simple-anti-bot-registration
 * 
 * 
 * initial release :      2015/01/24
 * latest modification :  2015/01/26
 * licence:               zlib (zlib/Libpng)
 *                        http://opensource.org/licenses/Zlib
 * authors:               GWRon (Ronny Otto)
 */

// NOTE: currently "have_to_check_user()" is called multiple times to
//       stay aware of potential changes to $pun_user inbetween of the
//       hooks the addon listens to

class addon_verysimpleantibot extends flux_addon
{
	public $language_file_loaded = false;
	public $chosen_question_index = -1;
	public $chosen_question_hash = "";
	public $chosen_question = "";

	//register to all hooks the addon is interested in
	//function is auto-called by the addon-system during boostrapping
	function register($manager)
	{
		if ($this->is_configured())
		{
			//Annotation by GWRon
			//TODO: to add the required field for quickposts new hooks
			//      are needed ("topic_before_header")
			 

			if ($this->have_to_check_user("registration"))
			{
				//hook into the registration process
				$manager->bind('register_before_header', array($this, 'hook_register_before_header'));
				$manager->bind('register_before_submit', array($this, 'hook_register_before_submit'));
				$manager->bind('register_before_validation', array($this, 'hook_register_before_validation'));
			}

			if ($this->have_to_check_user("posting"))
			{
				//hook into the posting process
				$manager->bind('post_before_header', array($this, 'hook_post_before_header'));
				$manager->bind('post_before_submit', array($this, 'hook_post_before_submit'));
				$manager->bind('post_before_validation', array($this, 'hook_post_before_validation'));

				//hook into the (quick-) posting process
				//for now we just reuse the same code of the posting_process
				//but without "header", as we do not modify the required_fields
				$manager->bind('quickpost_before_submit', array($this, 'hook_post_before_submit'));
				$manager->bind('quickpost_before_validation', array($this, 'hook_post_before_validation'));
			}
		}
	}

	//check if all needed configuration values are available
	function is_configured()
	{
		global $pun_config;

		if (empty($pun_config['vsab_enabled'])) return false;
		if (empty($pun_config['vsab_enabled_postings'])) return false;
		if (empty($pun_config['vsab_salt'])) return false;
		if (!isset($pun_config['vsab_minimum_posts_to_skip'])) return false;
		return true;
	}


	// === POSTING HOOKS ===

	//append the captcha as required field
	function hook_post_before_header()
	{
		if (!$this->have_to_check_user('posting'))
			return;

		//load language file so access to lang_addon_vsab is possible
		//without trouble
		$this->load_language_and_questions();
						
		global $required_fields, $lang_addon_vsab;
		$required_fields['vsab_answer'] = $lang_addon_vsab['title'];
	}

	//append the captcha/question to the post form
    function hook_post_before_submit()
    {
		if (!$this->have_to_check_user('posting'))
			return;

		$this->output_captcha_box('posting');
	}
    
	//validate the captcha before other validations take place
    function hook_post_before_validation()
    {
		//only validate checks if needed 
		if(!$this->have_to_check_user('posting'))
			return;

		//just previewing the post - skip validation check
		if (isset($_POST['preview']))
			return;

		//load questions if needed and validate answers
		$question_hash = isset($_POST['vsab_question']) ? trim($_POST['vsab_question']) : '';
		$question_answer = isset($_POST['vsab_answer']) ? trim($_POST['vsab_answer']) : '';

		if (!$this->verify_question_answer($question_hash, $question_answer))
		{
			//load language file
			$this->load_language_and_questions();

			global $errors, $lang_addon_vsab;
			$errors[] = $lang_addon_vsab['test failed'];
		}
	}


	// === REGISTRATION HOOKS ===

	//append the captcha as required field
    function hook_register_before_header()
    {
		if (!$this->have_to_check_user('registration'))
			return;

		//load language file so access to lang_addon_vsab is possible
		//without trouble
		$this->load_language_and_questions();
			
		global $required_fields, $lang_addon_vsab;
		$required_fields['vsab_answer'] = $lang_addon_vsab['title'];
	}

	//append the captcha/question to the registration form
    function hook_register_before_submit()
    {
		if (!$this->have_to_check_user('registration'))
			return;

		$this->output_captcha_box('registration');
	}

	//validate the captcha before other validations take place
	//also checks if a hidden fake-username-input-field was populated
    function hook_register_before_validation()
	{
		//if the hidden field username contains something, then it was
		//completed by a bot.
		if(!empty($_REQUEST['username']))
		{
			global $lang_register;
			message($lang_register['No new regs']);
		}

		//only validate checks if needed 
		if(!$this->have_to_check_user('registration'))
			return;

		//load questions if needed and validate answers
		$question_hash = isset($_POST['vsab_question']) ? trim($_POST['vsab_question']) : '';
		$question_answer = isset($_POST['vsab_answer']) ? trim($_POST['vsab_answer']) : '';

		if (!$this->verify_question_answer($question_hash, $question_answer))
		{
			//load language file
			$this->load_language_and_questions();

			global $errors, $lang_addon_vsab;
			$errors[] = $lang_addon_vsab['test failed'];
		}
	}


	// === HELPER FUNCTIONS ===

	//load a languagefile corresponding to the current user
	//loading is skipped if already done 
	function load_language_and_questions()
    {
		if($this->language_file_loaded)
			return false;

		global $pun_user;
		// Add language file and also load the contained questions
		if(file_exists(PUN_ROOT.'lang/'.$pun_user['language'].'/addon_verysimpleantibot.php'))
			require PUN_ROOT.'lang/'.$pun_user['language'].'/addon_verysimpleantibot.php';
		else
			require PUN_ROOT.'lang/English/addon_verysimpleantibot.php';

		$this->language_file_loaded = true;
	}

	//return the index of the currently chosen question
	//selects a question if not done yet
	function get_chosen_question_index()
	{
		if ($this->chosen_question_index < 0)
			do_choose_question();
		return $this->chosen_question_index;
	}

	//return the hash of the currently chosen question
	//selects a question if not done yet
	function get_chosen_question_hash()
	{
		if ($this->chosen_question_hash == '')
			do_choose_question();
		return $this->chosen_question_hash;
	}

	//return the text of the currently chosen question
	//selects a question if not done yet
	function get_chosen_question()
	{
		if ($this->chosen_question == '')
			do_choose_question();
		return $this->chosen_question;
	}

	//select and store question data out of the language file
	//corresponding to the current user
	function do_choose_question()
	{
		if (!$this->language_file_loaded)
			$this->load_language_and_questions();

		//check availability of questions
		global $addon_vsab_questions;
		if(isset($addon_vsab_questions) && count($addon_vsab_questions) > 0)
		{
			//load in the questions of the question=>answer array
			$questions = array_keys($addon_vsab_questions);
			//choose random question index and generate hash
			$this->chosen_question_index = rand(0, count($addon_vsab_questions)-1);
			$this->chosen_question = $questions[$this->chosen_question_index];
			$this->chosen_question_hash = $this->create_hash($questions[$this->chosen_question_index]);
			return true;
		}
		//no questions available
		return false;
	}

	//verify the given answer with the answer linkes to the also
	//provided hash of a question
	//The function returns TRUE if the answer was correct OR no valid
	//questions are defined at all.
	function verify_question_answer($question_hash, $question_answer)
	{
		if($question_hash == "" || $question_answer == "")
			return false;

		if (!$this->language_file_loaded)
			$this->load_language_and_questions();

		global $addon_vsab_questions;
		// if no questions are defined, validation is always successful 
		if(!isset($addon_vsab_questions) || count($addon_vsab_questions) == 0)
			return true;

		foreach ($addon_vsab_questions as $key=>$value)
			if ($this->is_valid_hash($question_hash, $key))
				if ($value == $question_answer)
					return true;
				else
					return false;
		
		//fail if the hash was invalid (outdated or manipulated)
		return false;
	}

	//return a salted hash for the given text
	//the salt contains the current day, so if the hash is created
	//a bit before midnight and validated later on, it will FAIL!
	//But using this avoids having a constant hash for requests with
	//the same text. 
	function create_hash($text)
	{
		global $pun_config;
		return md5($text . date('dmY') . $pun_config['vsab_salt']);
	}

	//returns whether a given hash is valid for the hash of the given
	//text in the current or the previous hour
	//this removes the problem of an invalid hash and shortens the
	//validity of a hash to 2 hours
	function is_valid_hash($hash, $text)
	{
		global $pun_config;
		//check current hour hash
		if($hash == md5($text . date('dmY') . $pun_config['vsab_salt']))
			return true;
		//check previous hour hash
		return ($hash == md5($text . date('dmY', strtotime('-1 hour')) . $pun_config['vsab_salt']));
	}

	//prints out the html code containing the captcha/question markup
	function output_captcha_box($action = '')
	{

		//load the language file (if not done yet)
		$this->load_language_and_questions();

		//select a random question and skip showing the captcha form
		//when failing (no questions available)
		if (!$this->do_choose_question())
			return;

		global $lang_addon_vsab, $lang_common;
		?>
		<div class="inform">
			<fieldset>
				<legend><?php echo $lang_addon_vsab['title'] ?></legend>
				<div class="infldset">
					<p><?php echo $lang_addon_vsab['info'] ?></p>
					<label class="required">
						<strong><?php echo sprintf($lang_addon_vsab['question'], $this->get_chosen_question()) ?></strong>
						<br />
						<strong><?php echo $lang_common['Required'] ?></strong>
						<input name="vsab_question" value="<?php echo	$this->get_chosen_question_hash() ?>" type="hidden" />
						<input name="vsab_answer" id="vsab_answer" type="text" size="10" maxlength="30" />
<?php if ($action == 'registration') : ?>
						<input type="hidden" name="username" value="" />
<?php endif; ?>
						<br />
					</label>
				</div>
			</fieldset>
		</div>
		<?php
	}		
	
	//skip checking if not needed for the given action
	function have_to_check_user($action = '')
	{
		global $pun_config, $pun_user;

        //addon disabled
		if ($pun_config['vsab_enabled']=='no') return false;

		switch($action)
		{
			case 'posting' :
				//validation of postings distinguishes between guests
				//and members - and not validating at all
				switch($pun_config['vsab_enabled_postings'])
				{
					//addon disabled for postings
					case 'no' :
						return false;

					//everyone has to get validated?
					case 'yes_for_all' :
						//everyone ... except moderators and admins
						if($pun_user['is_admmod']) return false;

						//could members skip validation if they reached
						//a specific post count?
						if ($pun_config['vsab_minimum_posts_to_skip'] > 0)
							if ($pun_user['num_posts'] >= $pun_config['vsab_minimum_posts_to_skip'])
								return false;
								
						//in all other cases: validate the user
						return true;

					//only guests have to get validated?
					case 'yes_for_guests' :
						return $pun_user['is_guest'];

					default :
						return true;
				}

			case 'registration' :
				//only check guests
				return $pun_user['is_guest'];

			default:
				return true;
		}
	}
}
