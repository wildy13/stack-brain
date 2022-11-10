<?php
header("Content-Type: text/plain");
?>User-agent: *
Disallow: <?php echo $Config['WebsitePath']; ?>/dashboard/
Disallow: <?php echo $Config['WebsitePath']; ?>/favorites
Disallow: <?php echo $Config['WebsitePath']; ?>/favorites/
Disallow: <?php echo $Config['WebsitePath']; ?>/forgot
Disallow: <?php echo $Config['WebsitePath']; ?>/goto/
Disallow: <?php echo $Config['WebsitePath']; ?>/json/
Disallow: <?php echo $Config['WebsitePath']; ?>/login
Disallow: <?php echo $Config['WebsitePath']; ?>/manage
Disallow: <?php echo $Config['WebsitePath']; ?>/new
Disallow: <?php echo $Config['WebsitePath']; ?>/notifications
Disallow: <?php echo $Config['WebsitePath']; ?>/register
Disallow: <?php echo $Config['WebsitePath']; ?>/reply
Disallow: <?php echo $Config['WebsitePath']; ?>/reset_password/
Disallow: <?php echo $Config['WebsitePath']; ?>/settings
Disallow: <?php echo $Config['WebsitePath']; ?>/tags/following
Disallow: <?php echo $Config['WebsitePath']; ?>/tags/following/
Disallow: <?php echo $Config['WebsitePath']; ?>/users/following
Disallow: <?php echo $Config['WebsitePath']; ?>/users/following/
Disallow: <?php echo $Config['WebsitePath']; ?>/upload_controller
<?php
$CurHost        = $CurProtocol . $_SERVER['HTTP_HOST'] . $Config['WebsitePath'];
$ItemPerSitemap = 30000;
echo 'Sitemap: ', $CurHost, "/sitemap-index.xml\n";
for ($i = 1; $i <= ceil($Config['NumTopics'] / $ItemPerSitemap); $i++)
	echo 'Sitemap: ', $CurHost, '/sitemap-topics-', $i, ".xml\n";
for ($i = 1; $i <= ceil(ceil($Config['NumTopics'] / $Config['TopicsPerPage']) / $ItemPerSitemap); $i++)
	echo 'Sitemap: ', $CurHost, '/sitemap-pages-', $i, ".xml\n";
for ($i = 1; $i <= ceil($Config['NumTags'] / $ItemPerSitemap); $i++)
	echo 'Sitemap: ', $CurHost, '/sitemap-tags-', $i, ".xml\n";
for ($i = 1; $i <= ceil($Config['NumUsers'] / $ItemPerSitemap); $i++)
	echo 'Sitemap: ', $CurHost, '/sitemap-users-', $i, ".xml\n";
?>