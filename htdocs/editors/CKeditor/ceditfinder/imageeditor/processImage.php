<?php
// This is first call, so we need to create copy of image for editing
	@unlink($imagename);
}
$editDirectory = getcwd() . "/edit/"; $imageName = $editDirectory . basename($origName); copy($origName, $imageName); }
// 90 or 270
	imagejpeg($image, $imageName, 100);
}
if ($extension == "gif") {
	imagegif($image, $imageName);
}
if ($extension == "png") {
	imagepng($image, $imageName);
}
}