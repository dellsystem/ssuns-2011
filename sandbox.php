<?
// So I don't have to type this form crap out 10 tens
include("delegations_array.php");
for ($i = 1; $i <= 10; $i++) {
	echo '<dd><select name="del_choice_' . $i . '" id="del_choice_' . $i . '">';
	echo "\n";
	foreach ($delegations as $key => $country) {
		if ($key == 0) {
			$country = 'Choose a country';
		}
		echo '<option value="' . $key . '" <!-- IF DEL_CHOICE_' . $i . ' == ' . $key . ' -->selected="selected"<!-- ENDIF -->>' . $country . '</option>';
		echo "\n";
	}
	echo '</select></dd>';
	echo "\n\n";
}

include("committees_array.php");
for ($i = 1; $i <= 3; $i++) {
	echo '<dd><select name="com_choice_' . $i . '" id="com_choice_' . $i . '">';
	echo "\n";
	foreach ($committees as $key => $committee) {
		if ($key == 0) {
			$committee = 'Choose a committee';
		}
		echo '<option value="' . $key . '" <!-- IF COM_CHOICE_' . $i . ' == ' . $key . ' -->selected="selected"<!-- ENDIF -->>' . $committee . '</option>';
		echo "\n";
	}
	echo '</select></dd>';
	echo "\n\n";
}
?>
