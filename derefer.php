<html>
<head>
<? if($url) { ?>
<META HTTP-EQUIV="refresh" CONTENT="0; URL=<? echo urldecode($url); ?>">
<? } ?>
</head>
<body>
<? if($url) { ?>
<p>Redirecting to <a href="<? echo urldecode($url).'">'.urldecode($url).'</a>'; ?></p>
<? } else { ?>
<p>No URL specified.</p>
<? } ?>
</body>
</html>
