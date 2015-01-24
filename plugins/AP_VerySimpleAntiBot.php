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
 * latest modification :  2015/01/24
 * licence:               zlib (zlib/Libpng)
 *                        http://opensource.org/licenses/Zlib
 * authors:               GWRon (Ronny Otto)
 */

// GWRon: I used "select boxes" for the options to allow to extend the
//        options more easily ("just guests", "guests and users", "none").


// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
    exit;

// Tell admin_loader.php that this is indeed a plugin and that it is loaded
define('PUN_PLUGIN_LOADED', 1);


// Store the config
if (isset($_POST['process_form']))
{
    $conf['enabled'] = isset($_POST['vsab_enabled']) ? pun_trim($_POST['vsab_enabled']) : "no";
    $conf['enabled_postings'] = isset($_POST['vsab_enabled_postings']) ? pun_trim($_POST['vsab_enabled_postings']) : "no";

    foreach ($conf as $key => $value)
    {
        $key = 'vsab_'.$key;

        if (isset($pun_config[$key]))
            $db->query('UPDATE '.$db->prefix.'config SET conf_value = \''.$db->escape($value).'\' WHERE conf_name = \''.$db->escape($key).'\'') or error('Unable to update config value for '.$key, __FILE__, __LINE__, $db->error());
        else
            $db->query('INSERT INTO '.$db->prefix.'config (conf_name, conf_value) VALUES (\''.$db->escape($key).'\', \''.$db->escape($value).'\')') or error('Unable to store config value for '.$key, __FILE__, __LINE__, $db->error());
    }

    // Regenerate the config cache
    if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
        require PUN_ROOT.'include/cache.php';

    generate_config_cache();

    redirect('admin_loader.php?plugin=AP_VerySimpleAntiBot.php', 'Settings saved successfully. Redirecting...');
}


// load language file
if(file_exists(PUN_ROOT.'lang/'.$pun_user['language'].'/addon_verysimpleantibot.php'))
	require PUN_ROOT.'lang/'.$pun_user['language'].'/addon_verysimpleantibot.php';
else
	require PUN_ROOT.'lang/English/addon_verysimpleantibot.php';

// Display the admin navigation menu
generate_admin_menu($plugin);

// set default configuration
if(!isset($pun_config['vsab_enabled'])) $pun_config['vsab_enabled'] = 'no';
if(!isset($pun_config['vsab_enabled_postings'])) $pun_config['vsab_enabled_postings'] = 'no';

?>

<div class="blockform">
    <h2><span>VSAB Very Simple AntiBot</span></h2>
    <div class="box">
        <form id="vsab" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
            <div class="inform">
                <fieldset>
                    <legend><?php echo $lang_addon_admin_vsab['AP title'] ?></legend>
                    <div class="infldset">
                        <p><?php echo $lang_addon_admin_vsab['AP description'] ?></p>
                        <table class="aligntop" cellspacing="0">
                            <tr>
                                <th scope="row"><?php echo $lang_addon_admin_vsab['Enabled'] ?></th>
                                <td>
									<select name="vsab_enabled">
										<option value="yes" <?php if ($pun_config['vsab_enabled'] == 'yes') echo "selected"; ?>><?php echo $lang_admin_common['Yes'] ?></option>
										<option value="no" <?php if ($pun_config['vsab_enabled'] != 'yes') echo "selected"; ?>><?php echo $lang_admin_common['No'] ?></option>
									</select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo $lang_addon_admin_vsab['Enabled for postings'] ?></th>
                                <td style="width:75%;">
									<select name="vsab_enabled_postings">
										<option value="yes" <?php if ($pun_config['vsab_enabled_postings'] == 'yes') echo "selected"; ?>><?php echo $lang_admin_common['Yes'] ?></option>
										<option value="no" <?php if ($pun_config['vsab_enabled_postings'] != 'yes') echo "selected"; ?>><?php echo $lang_admin_common['No'] ?></option>
									</select>
                                </td>
                            </tr>
                        </table>
                    </div>
                </fieldset>
            </div>
            <p class="submitend"><input type="submit" name="process_form" value="<?php echo $lang_admin_common['Save'] ?>" /></p>
        </form>
    </div>
</div>
