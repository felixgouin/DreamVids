<nav class="tabs four">
	<ul>
		<li <?php echo (isset($current) && $current == 'channels') ? 'class="current"' : ''; ?>><a href="<?php echo WEBROOT.'account/channels'; ?>">Chaînes</a></li>
		<li <?php echo (isset($current) && $current == 'account') ? 'class="current"' : ''; ?>><a href="<?php echo WEBROOT.'account/infos'; ?>">Informations</a></li>
		<li <?php echo (isset($current) && $current == 'password') ? 'class="current"' : ''; ?>><a href="<?php echo WEBROOT.'account/password'; ?>">Mot de passe</a></li>
		<li <?php echo (isset($current) && $current == 'videos') ? 'class="current"' : ''; ?>><a href="<?php echo WEBROOT.'account/videos'; ?>">Vidéos</a></li>
		<li <?php echo (isset($current) && $current == 'messages') ? 'class="current"' : ''; ?>><a href="<?php echo WEBROOT.'account/messages'; ?>">Messagerie</a></li>
	</ul>
</nav>