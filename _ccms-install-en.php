<?
/*
 * Custodian CMS (CCMS) Installer
 *
 * Automatically download and deploy the templates for CCMS using PHP and Git.
 *
 * @version 0.1
 * @link    https://github.com/modusinternet/Custodian-CMS-Installer/
 *
 * @min server require
 * PHP v5.4+
 * `git` and `rsync` are required on the server that's running the script.
 *		- Optionally: `tar` is required for backup functionality (`BACKUP_DIR` option).
 *		- Optionally: `composer` is required for composer functionality (`USE_COMPOSER` option).
 * System user running PHP (e.g. `www-data`) needs to have the necessary access permissions to the `TMP_DIR` and `TARGET_DIR` locations.
 */

/*
 * Protect the script from unauthorized access by using a secret access token.
 * If it's not present in the access URL as a GET variable named `sat`
 * e.g. ccms-install-en.php?sat=ChangeThisStrfdsgfingToSomethingElse...lse the script is not going to deploy.
 *
 * @var string
 */
define('SECRET_ACCESS_TOKEN', 'ChangeThisStrfdsgfingToSomethingElse');

/**
 * The address of the remote Git repository that contains the code that's being
 * deployed.
 * If the repository is private, you'll need to use the SSH address.
 *
 * @var string
 */
 /*define('REMOTE_REPOSITORY', 'https://github.com/modusinternet/custodian-cms.git');*/
 define('REMOTE_REPOSITORY', 'https://github.com/modusinternet/custodiancms_dev.git');

/**
 * The branch that's being deployed.
 * Must be present in the remote repository.
 *
 * @var string
 */
define('BRANCH', 'master');

/**
 * The location that the code is going to be deployed to.
 * Don't forget the trailing slash!
 *
 * @var string Full path including the trailing slash
 */
define('TARGET_DIR', getcwd() . '/');

/**
 * Whether to delete the files that are not in the repository but are on the
 * local (server) machine.
 *
 * !!! WARNING !!! This can lead to a serious loss of data if you're not
 * careful. All files that are not in the repository are going to be deleted,
 * except the ones defined in EXCLUDE section.
 * BE CAREFUL!
 *
 * @var boolean
 */
define('DELETE_FILES', false);

/**
 * The directories and files that are to be excluded when updating the code.
 * Normally, these are the directories containing files that are not part of
 * code base, for example user uploads or server-specific configuration files.
 * Use rsync exclude pattern syntax for each element.
 *
 * @var serialized array of strings
 */
define('EXCLUDE', serialize(array(
	'.git',
)));

/**
 * Temporary directory we'll use to stage the code before the update. If it
 * already exists, script assumes that it contains an already cloned copy of the
 * repository with the correct remote origin and only fetches changes instead of
 * cloning the entire thing.
 *
 * @var string Full path including the trailing slash
 */
define('TMP_DIR', '/tmp/spgd-'.md5(REMOTE_REPOSITORY).'/');

/**
 * Whether to remove the TMP_DIR after the deployment.
 * It's useful NOT to clean up in order to only fetch changes on the next
 * deployment.
 */
define('CLEAN_UP', true);

/**
 * Output the version of the deployed code.
 *
 * @var string Full path to the file name
 */
define('VERSION_FILE', TMP_DIR.'VERSION');

/**
 * Time limit for each command.
 *
 * @var int Time in seconds
 */
define('TIME_LIMIT', 30);

/**
 * OPTIONAL
 * Backup the TARGET_DIR into BACKUP_DIR before deployment.
 *
 * @var string Full backup directory path e.g. `/tmp/`
 */
define('BACKUP_DIR', false);

/**
 * OPTIONAL
 * Whether to invoke composer after the repository is cloned or changes are
 * fetched. Composer needs to be available on the server machine, installed
 * globaly (as `composer`). See http://getcomposer.org/doc/00-intro.md#globally
 *
 * @var boolean Whether to use composer or not
 * @link http://getcomposer.org/
 */
define('USE_COMPOSER', false);

/**
 * OPTIONAL
 * The options that the composer is going to use.
 *
 * @var string Composer options
 * @link http://getcomposer.org/doc/03-cli.md#install
 */
define('COMPOSER_OPTIONS', '--no-dev');

/**
 * OPTIONAL
 * The COMPOSER_HOME environment variable is needed only if the script is
 * executed by a system user that has no HOME defined, e.g. `www-data`.
 *
 * @var string Path to the COMPOSER_HOME e.g. `/tmp/composer`
 * @link https://getcomposer.org/doc/03-cli.md#composer-home
 */
define('COMPOSER_HOME', false);

/**
 * OPTIONAL
 * Email address to be notified on deployment failure.
 *
 * @var string A single email address, or comma separated list of email addresses
 *      e.g. 'someone@example.com' or 'someone@example.com, someone-else@example.com, ...'
 */
define('EMAIL_ON_ERROR', false);

define('CONFIG_FILE', __FILE__);

// If there's authorization error, set the correct HTTP header.
if (!isset($_GET['sat']) || $_GET['sat'] !== SECRET_ACCESS_TOKEN || SECRET_ACCESS_TOKEN === 'ChangeThisStringToSomethingElse') {
	header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden", true, 403);
} else {
	header("Content-Type: text/html; charset=UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1" />
		<meta name="author" content="Developed by Vincent Hallberg of modusinternet.com." />
		<link rel="dns-prefetch" href="https://fonts.googleapis.com" />
		<title>Custodian CMS (CCMS) Installer</title>
		<meta name="description" content="A small program to help download and install the Custodian CMS (CCMS) templates.  https://github.com/modusinternet/Custodian-CMS-Installer" />
		<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i&amp;subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese" rel="stylesheet">
		<style>
			*, ::after, ::before {
				margin: 0;
				padding: 0;
				-webkit-box-sizing: border-box;
				-moz-box-sizing: border-box;
				box-sizing: border-box;
			}

			body {
				color: #666;
				font: 100 18px/2.4 'Open Sans',sans-serif;
			}

			button {
				background-color: #ec7f27;
				border: none;
				border-radius: 5px;
				color: white;
				cursor: pointer;
				display: inline-block;
				font-size: 16px;
				margin: 4px 2px;
				padding: 15px 32px;
				text-align: center;
				text-decoration: none;
			}

			button:hover { box-shadow: 0 12px 16px 0 rgba(0,0,0,0.24), 0 17px 50px 0 rgba(0,0,0,0.19); }

			h1, h2, h3, h4, h5, h6 {
				color: #4F9F43;
				font-weight: 100;
			}

			h1 { line-height: 2em; }

			h2 { line-height: 1.8em; }

			ul, p { margin-bottom: 1rem; }

			main {
				margin: 20px;
				position: absolute;
			}

			.red { color: #ff0000; }
			.oj { color: #ec7f27; }
			.italic { font-style: italic; }
		</style>
	</head>
	<body>
		<main>
<?if(!isset($_GET["sat"])): ?>
			<h1 class="red">ACCESS DENIED!</h1>
			There was no SECRET_ACCESS_TOKEN (sat) argument found in your URI.<br />
			i.e.  https://yourdomain.com/ccms-setup-en.php?sat=aabbYourNewStringccdd
<? elseif($_GET["sat"] !== SECRET_ACCESS_TOKEN): ?>
			<h1 class="red">ACCESS DENIED!</h1>
			The SECRET_ACCESS_TOKEN for this template and the 'sat' argument in your URI do not match.  Update one or the other so they both match and try again.
<? elseif(SECRET_ACCESS_TOKEN === "ChangeThisStringToSomethingElse"): ?>
			<h1 class="red">ACCESS DENIED!</h1>
			Change the SECRET_ACCESS_TOKEN value in this template from it's default value and try again.
<? elseif($_GET["continue"] === "1"): ?>
<pre>
Checking environment ...
Running as <b><?=trim(shell_exec('whoami')); ?></b>.
<?
// Check if the required programs are available
$requiredBinaries = array('git', 'rsync');
if (defined('BACKUP_DIR') && BACKUP_DIR !== false) {
	$requiredBinaries[] = 'tar';
	if (!is_dir(BACKUP_DIR) || !is_writable(BACKUP_DIR)) {
		header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
		die(sprintf('<div class="error">BACKUP_DIR `%s` does not exists or is not writeable.</div>', BACKUP_DIR));
	}
}
if (defined('USE_COMPOSER') && USE_COMPOSER === true) {
	$requiredBinaries[] = 'composer --no-ansi';
}
foreach ($requiredBinaries as $command) {
	$path = trim(shell_exec('which '.$command));
	if ($path == '') {
		header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
		die(sprintf('<div class="error"><b>%s</b> not available. It needs to be installed on the server for this script to work.</div>', $command));
	} else {
		$version = explode("\n", shell_exec($command.' --version'));
		printf('<b>%s</b> : %s'."\n"
			, $path
			, $version[0]
		);
	}
}
?>
Environment OK.
Using configuration defined in <?php echo CONFIG_FILE."\n"; ?>
Deploying <?php echo REMOTE_REPOSITORY; ?> <?php echo BRANCH."\n"; ?>
to <?php echo TARGET_DIR; ?> ...
<?php
// The commands
$commands = array();
// ========================================[ Pre-Deployment steps ]===
if (!is_dir(TMP_DIR)) {
	// Clone the repository into the TMP_DIR
	$commands[] = sprintf(
		'git clone --depth=1 --branch %s %s %s'
		, BRANCH
		, REMOTE_REPOSITORY
		, TMP_DIR
	);
} else {
	// TMP_DIR exists and hopefully already contains the correct remote origin
	// so we'll fetch the changes and reset the contents.
	$commands[] = sprintf(
		'git --git-dir="%s.git" --work-tree="%s" fetch --tags origin %s'
		, TMP_DIR
		, TMP_DIR
		, BRANCH
	);
	$commands[] = sprintf(
		'git --git-dir="%s.git" --work-tree="%s" reset --hard FETCH_HEAD'
		, TMP_DIR
		, TMP_DIR
	);
}
// Update the submodules
$commands[] = sprintf(
	'git submodule update --init --recursive'
);
// Describe the deployed version
if (defined('VERSION_FILE') && VERSION_FILE !== '') {
	$commands[] = sprintf(
		'git --git-dir="%s.git" --work-tree="%s" describe --always > %s'
		, TMP_DIR
		, TMP_DIR
		, VERSION_FILE
	);
}
// Backup the TARGET_DIR
// without the BACKUP_DIR for the case when it's inside the TARGET_DIR
if (defined('BACKUP_DIR') && BACKUP_DIR !== false) {
	$commands[] = sprintf(
		"tar --exclude='%s*' -czf %s/%s-%s-%s.tar.gz %s*"
		, BACKUP_DIR
		, BACKUP_DIR
		, basename(TARGET_DIR)
		, md5(TARGET_DIR)
		, date('YmdHis')
		, TARGET_DIR // We're backing up this directory into BACKUP_DIR
	);
}
// Invoke composer
if (defined('USE_COMPOSER') && USE_COMPOSER === true) {
	$commands[] = sprintf(
		'composer --no-ansi --no-interaction --no-progress --working-dir=%s install %s'
		, TMP_DIR
		, (defined('COMPOSER_OPTIONS')) ? COMPOSER_OPTIONS : ''
	);
	if (defined('COMPOSER_HOME') && is_dir(COMPOSER_HOME)) {
		putenv('COMPOSER_HOME='.COMPOSER_HOME);
	}
}
// ==================================================[ Deployment ]===
// Compile exclude parameters
$exclude = '';
foreach (unserialize(EXCLUDE) as $exc) {
	$exclude .= ' --exclude='.$exc;
}
// Deployment command
$commands[] = sprintf(
	'rsync -rltgoDzvO %s %s %s %s'
	, TMP_DIR
	, TARGET_DIR
	, (DELETE_FILES) ? '--delete-after' : ''
	, $exclude
);
// =======================================[ Post-Deployment steps ]===
// Remove the TMP_DIR (depends on CLEAN_UP)
if (CLEAN_UP) {
	$commands['cleanup'] = sprintf(
		'rm -rf %s'
		, TMP_DIR
	);
}
// =======================================[ Run the command steps ]===
$output = '';
foreach ($commands as $command) {
	set_time_limit(TIME_LIMIT); // Reset the time limit for each command
	if (file_exists(TMP_DIR) && is_dir(TMP_DIR)) {
		chdir(TMP_DIR); // Ensure that we're in the right directory
	}
	$tmp = array();
	exec($command.' 2>&1', $tmp, $return_code); // Execute the command
	// Output the result
	printf('<span class="prompt">$</span> <span class="command">%s</span>
<div class="output">%s</div>
'
		, htmlentities(trim($command))
		, htmlentities(trim(implode("\n", $tmp)))
	);
	$output .= ob_get_contents();
	ob_flush(); // Try to output everything as it happens
	// Error handling and cleanup
	if ($return_code !== 0) {
		header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
		printf('<div class="error">
Error encountered!
Stopping the script to prevent possible data loss.
CHECK THE DATA IN YOUR TARGET DIR!
</div>
'
		);
		if (CLEAN_UP) {
			$tmp = shell_exec($commands['cleanup']);
			printf('Cleaning up temporary files ...
<span class="prompt">$</span> <span class="command">%s</span>
<div class="output">%s</div>
'
				, htmlentities(trim($commands['cleanup']))
				, htmlentities(trim($tmp))
			);
		}
		$error = sprintf(
			'Deployment error on %s using %s!'
			, $_SERVER['HTTP_HOST']
			, __FILE__
		);
		error_log($error);
		if (EMAIL_ON_ERROR) {
			$output .= ob_get_contents();
			$headers = array();
			$headers[] = sprintf('From: Custodian CMS Installer script <do-no=reply@%s>', $_SERVER['HTTP_HOST']);
			$headers[] = sprintf('X-Mailer: PHP/%s', phpversion());
			mail(EMAIL_ON_ERROR, $error, strip_tags(trim($output)), implode("\r\n", $headers));
		}
		break;
	}
}
?>Done.
</pre>
<form action="/" method="get" style="padding: 20px 0;">
	<button>Continue to Step 2: Template and Database Setup</button>
</form>
<? else: ?>
			<h1>Custodian CMS (CCMS) Installer</h1>
			<h2>Step 1: Template download</h2>
			Welcome to the CCMS installer.  This part of the process involves downloading the CCMS templates from GitHub and nothing more.  Further testing of your servers environment and template/database configuration will take place in Step 2.  For now, please read and confirm the download settings before proceeding.<br />
			Note: All settings can be customized by editing this template directly if neccessary.<br />
			<br />

			<h3>Git repository?</h3>
			<span class="italic"><?=REMOTE_REPOSITORY; ?></span><br />
			<br />

			<h3>Branch?</h3>
			<span class="italic"><?=BRANCH; ?></span><br />
			<br />

			<h3>Target location?</h3>
			<span class="italic"><?=TARGET_DIR; ?></span><br />
			<br />

			<h3>Delete local files that are not in the repository?</h3>
			<span class="italic"><? if(DELETE_FILES == FALSE) echo "No"; else echo "Yes"; ?></span><br />
			<br />

			<h3>Directories and files that are to be EXCLUDED when updating the code?</h3>
			<span class="italic"><pre><?=print_r(unserialize(EXCLUDE)); ?></pre></span><br />
			<br />

			<h3>Temporary directory used to stage the code?</h3>
			<span class="italic"><?=TMP_DIR; ?></span><br />
			<br />

			<h3>Whether to remove the TMP_DIR after the deployment?</h3>
			<span class="italic"><? if(CLEAN_UP == TRUE) echo "Yes"; else echo "No"; ?></span><br />
			<br />

			<h3>Time limit for each command?</h3>
			<span class="italic"><?=TIME_LIMIT; ?></span><br />
			<br />

			<h3>Backup the TARGET_DIR into BACKUP_DIR before deployment? (Optional)</h3>
			<span class="italic"><? if(BACKUP_DIR == FALSE) echo "No"; else echo "Yes"; ?></span><br />
			<br />

			<h3>Whether to invoke composer after the repository is cloned or changes are fetched? (Optional)</h3>
			<span class="italic"><? if(USE_COMPOSER == FALSE) echo "No"; else echo "Yes"; ?></span><br />
			<br />

			<h3>The options that the composer is going to use? (Optional)</h3>
			<span class="italic"><?=COMPOSER_OPTIONS; ?></span><br />
			<br />

			<h3>The COMPOSER_HOME environment variable is needed only if the script is executed by a system user that has no HOME defined, e.g. `www-data`? (Optional)</h3>
			<span class="italic"><? if(COMPOSER_HOME == FALSE) echo "No"; else echo "Yes"; ?></span><br />
			<br />

			<h3>Email address to be notified on deployment failure? (Optional)</h3>
			<span class="italic"><? if(EMAIL_ON_ERROR == FALSE) echo "No"; else echo "Yes"; ?></span><br />
			<br />

			<div style="background-color: #df1f30; border: none; border-radius: 5px; color: white; margin: 10px 0px; padding: 15px 32px;">
				<h3 style="color: white;">WARNING!</h3>
				<p>This process will overwrite all existing CCMS templates in the 'Target location'. <span style="text-decoration: underline;">Including</span> the /.htaccess, /index.php and /ccmstpl/index.html files. But does <span style="text-decoration: underline;">not</span> overwrite files that are not part of the CCMS repository like /ccmslib/yourDomainsLibraries.php, /ccmspre/config.php, /ccmspre/whitelist_public.php or /ccmstpl/yourTemplate.php.</p>
				<p>This installer is designed only to help install, reinstall, upgrade or recover lost/damaged files which are part of the CCMS code base.</p>
				<p>This step <span style="text-decoration: underline;">does not</span> touch your database in any way.</p>
			</div>

			<div style="position: relative; margin-bottom: 10px; max-width: 460px;">
				<div>
					<form action="<?=$_SERVER["SCRIPT_NAME"];?>" method="get">
						<input type="hidden" name="sat" value="<?=$_REQUEST["sat"];?>"/>
						<button>Reload</button>
					</form>
				</div>
				<div style="position: absolute; right: 0px; top: 0px;">
					<form action="<?=$_SERVER["SCRIPT_NAME"];?>" method="get">
						<input type="hidden" name="sat" value="<?=$_REQUEST["sat"];?>"/>
						<input type="hidden" name="continue" value="1"/>
						<button>Continue</button>
					</form>
				</div>
			</div>
<? endif; ?>
		</main>
	</body>
</html>
