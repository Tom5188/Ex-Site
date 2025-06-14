<?php

namespace App;

class MakePoster
{
	public function createPoster($config = array(), $filename = "")
	{
		$_var_0 = array("left" => 0, "top" => 0, "right" => 0, "bottom" => 0, "width" => 100, "height" => 100, "opacity" => 100);
		$_var_1 = array("text" => "", "left" => 0, "top" => 0, "fontSize" => 32, "fontColor" => "255,255,255", "angle" => 0);
		$_var_2 = $config["background"];
		$_var_3 = getimagesize($_var_2);
		$_var_4 = "imagecreatefrom" . image_type_to_extension($_var_3[2], false);
		$_var_2 = $_var_4($_var_2);
		$_var_5 = imagesx($_var_2);
		$_var_6 = imagesy($_var_2);
		$_var_7 = imageCreatetruecolor($_var_5, $_var_6);
		$_var_8 = imagecolorallocate($_var_7, 0, 0, 0);
		imagefill($_var_7, 0, 0, $_var_8);
		imagecopyresampled($_var_7, $_var_2, 0, 0, 0, 0, imagesx($_var_2), imagesy($_var_2), imagesx($_var_2), imagesy($_var_2));
		if (!empty($config["image"])) {
			foreach ($config["image"] as $_var_9 => $_var_10) {
				$_var_10 = array_merge($_var_0, $_var_10);
				$_var_11 = getimagesize($_var_10["url"]);
				$_var_12 = "imagecreatefrom" . image_type_to_extension($_var_11[2], false);
				if ($_var_10["stream"]) {
					$_var_11 = getimagesizefromstring($_var_10["url"]);
					$_var_12 = "imagecreatefromstring";
				}
				$_var_13 = $_var_12($_var_10["url"]);
				$_var_14 = $_var_11[0];
				$_var_15 = $_var_11[1];
				$_var_16 = imagecreatetruecolor($_var_10["width"], $_var_10["height"]);
				imagefill($_var_16, 0, 0, $_var_8);
				imagecopyresampled($_var_16, $_var_13, 0, 0, 0, 0, $_var_10["width"], $_var_10["height"], $_var_14, $_var_15);
				$_var_10["left"] = $_var_10["left"] < 0 ? $_var_5 - abs($_var_10["left"]) - $_var_10["width"] : $_var_10["left"];
				$_var_10["top"] = $_var_10["top"] < 0 ? $_var_6 - abs($_var_10["top"]) - $_var_10["height"] : $_var_10["top"];
				imagecopymerge($_var_7, $_var_16, $_var_10["left"], $_var_10["top"], $_var_10["right"], $_var_10["bottom"], $_var_10["width"], $_var_10["height"], $_var_10["opacity"]);
			}
		}
		if (!empty($config["text"])) {
			foreach ($config["text"] as $_var_9 => $_var_10) {
				$_var_10 = array_merge($_var_1, $_var_10);
				list($_var_17, $_var_18, $_var_19) = explode(",", $_var_10["fontColor"]);
				$_var_20 = imagecolorallocate($_var_7, $_var_17, $_var_18, $_var_19);
				$_var_10["left"] = $_var_10["left"] < 0 ? $_var_5 - abs($_var_10["left"]) : $_var_10["left"];
				$_var_10["top"] = $_var_10["top"] < 0 ? $_var_6 - abs($_var_10["top"]) : $_var_10["top"];
				imagettftext($_var_7, $_var_10["fontSize"], $_var_10["angle"], $_var_10["left"], $_var_10["top"], $_var_20, $_var_10["fontPath"], $_var_10["text"]);
			}
		}
		if (!empty($filename)) {
			$_var_13 = imagejpeg($_var_7, $filename, 90);
			imagedestroy($_var_7);
			if (!$_var_13) {
				return false;
			}
			return $filename;
		} else {
			imagejpeg($_var_7);
			imagedestroy($_var_7);
		}
	}
}