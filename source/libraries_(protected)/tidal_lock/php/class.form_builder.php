<?php

	class formBuilder_TL {
		
		public function input($type, $name, $value = null, $mandatory = false, $labelPlaceholder = null, $class = null, $options = null, $maxlength = null, $otherAttributes = null, $truncateLabel = null, $eventHandlers = null) {

			if (!$type) {
				errorManager_TL::addError("No element type specified.");
				return false;
			}
			
			if ($labelPlaceholder) {
				$result = explode('|', $labelPlaceholder);
				if (isset($result[0])) $label = $result[0];
				if (isset($result[1])) $placeholder = $result[1];
			}
			
			if (!isset($label)) $label = null;
			if (!isset($placeholder)) $placeholder = null;
			
			switch($type) {
				/* ------------------------- */
					case 'text':
					case 'password':
					case 'hidden':
					case 'number':
					case 'number_without_spin_buttons':
					case 'url':
					case 'email':
					case 'tel':
						// validate
							if (!$name) break; // no name
							if ($type == 'number' && $value && !is_numeric($value)) $value = 0; // can't put a non-numeric value in a value field
							if ($type == 'email' && $value && substr_count($value, '@') < 1 && substr_count($value, '.') < 1) break; // email value is invalid
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
						// return
							if ($type == 'number_without_spin_buttons') $field = "<input type='text' name='" . $name . "' id='" . $name . "'";
							else $field = "<input type='" . $type . "' name='" . $name . "' id='" . $name . "'";
							if ($placeholder) $field .= " placeholder='" . htmlspecialchars($placeholder, ENT_QUOTES) . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($maxlength) $field .= " maxlength='" . $maxlength . "'";
							if ($value) $field .= " value='" . htmlspecialchars($value, ENT_QUOTES) . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($type == 'number') {
								$eventHandlers['onChange'] = "if (isNaN(this.value)) this.value = 0; " . $eventHandlers['onChange'];
								if (!is_null(@$otherAttributes['max'])) $eventHandlers['onChange'] = "if (this.value && this.value > " . floatval($otherAttributes['max']) . ") this.value = " . floatval($otherAttributes['max']) . "; " . $eventHandlers['onChange'];
								if (!is_null(@$otherAttributes['min'])) $eventHandlers['onChange'] = "if (this.value && this.value < " . floatval($otherAttributes['min']) . ") this.value = " . floatval($otherAttributes['min']) . "; " . $eventHandlers['onChange'];
							}
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " />";
							return $field;
							break;
				/* ------------------------- */
					case 'uneditable':
						// validate
							if (!$name) break; // no name
							if (!$value) break; // no value
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
						// return
							$field = "<div";
							if ($class) $field .= " class='" . $class . "'";
							$field .= ">";
							$field .= "<b>" . htmlspecialchars($value, ENT_QUOTES) . "</b>";
							$field .= "<input type='hidden' name='" . $name . "' id='" . $name . "'";
							$field .= " value='" . htmlspecialchars($value, ENT_QUOTES) . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							$field .= " />";
							$field .= "</div>";
							return $field;
							break;
				/* ------------------------- */
					case 'uneditable_bootstrap':
						// validate
							if (!$name) break; // no name
							if (!$value) break; // no value
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
						// return
							$field = "<span";
							$field .= " class='" . trim($class . ' uneditable-input') . "'>";
							$field .= htmlspecialchars($value, ENT_QUOTES);
							$field .= "</span>";
							$field .= "<input type='hidden' name='" . $name . "' id='" . $name . "'";
							$field .= " value='" . htmlspecialchars($value, ENT_QUOTES) . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							$field .= " />";
							return $field;
							break;
				/* ------------------------- */
					case 'password_with_health_meter':
						// validate
							if (!$name) break; // no name
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
						// return
							$field = "<div><input type='password' name='" . $name . "' id='" . $name . "'";
							if ($placeholder) $field .= " placeholder='" . htmlspecialchars($placeholder, ENT_QUOTES) . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($maxlength) $field .= " maxlength='" . $maxlength . "'";
							if ($value) $field .= " value='" . htmlspecialchars($value, ENT_QUOTES) . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " /></div>";
							$field .= "<div id='healthMeterContainer' class='hidden'><div id='healthMeter' class='" . $class . "'></div></div>";
							return $field;
							break;
				/* ------------------------- */
					case 'search':
						// validate
							if (!$name) break; // no name
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
						// return
							$field = "<input type='search' name='" . $name . "' id='" . $name . "' results='0'";
							if ($placeholder) $field .= " placeholder='" . htmlspecialchars($placeholder, ENT_QUOTES) . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($maxlength) $field .= " maxlength='" . $maxlength . "'";
							if ($value) $field .= " value='" . htmlspecialchars($value, ENT_QUOTES) . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " />";
							return $field;
							break;
				/* ------------------------- */
						case 'select':
						// validate
							if (!$name) break; // no name
							if (count($options) < 1) break; // no options to display in select box
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
						// return
							$field = "<select name='" . $name . "' id='" . $name . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " />";
							if ($label && !$mandatory) {
								$field .= "<option value=''>" . $label . "</option>";
								$field .= "<option value=''>--</option>";
							}
							foreach ($options as $optionValue => $optionLabel) {
								$field .= "<option value='" . $optionValue . "'";
								if ($value && $optionValue == $value) $field .= " selected";
								$field .= ">";
								if ($truncateLabel && $truncateLabel < strlen($optionLabel) - 3) $label = substr($optionLabel, 0, $truncateLabel) . '...';
								$field .= $optionLabel;
								$field .= "</option>";
							}
							$field .= "</select>";
							return $field;
							break;
				/* ------------------------- */
					case 'yesno_bootstrap_switch':
						// validate
							if (!$name) break; // no name
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
						// initialize
							$field = '';
							$value = floatval(@$value);
						// return
							$field .= "<div class='make-switch'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if (!$otherAttributes['data-on']) $field .= " data-on='info'";
							if (!$otherAttributes['data-on-label']) $field .= " data-on-label='YES'";
							if (!$otherAttributes['data-off-label']) $field .= " data-off-label='NO'";
							$field .= ">";
							$field .= "<input type='checkbox' name='" . $name . "' id='" . $name . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($value) $field .= " checked='checked'";
							$field .= " />";
							$field .= "</div>";
							return $field;
							break;
				/* ------------------------- */
					case 'percentage':
						// validate
							if (!$name) break; // no name
							if ($value && !is_numeric($value)) break; // can't put a non-numeric value in a value field
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
						// initialize
							if (!$eventHandlers) $eventHandlers = array();
							$eventHandlers['onChange'] = "if (isNaN(this.value)) { this.value = 0; } if (parseInt(this.value) > 100) { this.value = 100; } if (parseInt(this.value) < 0) { this.value = 0; } " . @$eventHandlers['onChange'];
						// return
							$field = "<input type='number' name='" . $name . "' id='" . $name . "'";
							if ($placeholder) $field .= " placeholder='" . htmlspecialchars($placeholder, ENT_QUOTES) . "'";
							$field .= " class='" . trim("input-mini " . $class) . "'";
							if ($value) $field .= " value='" . floatval($value) . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " />";
							return $field;
							break;
				/* ------------------------- */
					case 'percentage_bootstrap':
						// validate
							if (!$name) break; // no name
							if ($value && !is_numeric($value)) break; // can't put a non-numeric value in a value field
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
						// initialize
							if (!$eventHandlers) $eventHandlers = array();
							$eventHandlers['onChange'] = "if (isNaN(this.value)) { this.value = 0; } if (parseInt(this.value) > 100) { this.value = 100; } if (parseInt(this.value) < 0) { this.value = 0; } " . @$eventHandlers['onChange'];
						// return
							$field = "<div class='input-append'>";
							$field .= "<input type='text' name='" . $name . "' id='" . $name . "'";
							if ($placeholder) $field .= " placeholder='" . htmlspecialchars($placeholder, ENT_QUOTES) . "'";
							$field .= " class='" . trim("input-mini " . $class) . "'";
							if ($value) $field .= " value='" . floatval($value) . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " />";
							$field .= "<span class='add-on'>%</span></div>";
							return $field;
							break;
				/* ------------------------- */
					case 'dollars':
						// validate
							if (!$name) break; // no name
							$value = str_replace(',', '', $value);
							if ($value && !is_numeric($value)) break; // can't put a non-numeric value in a value field
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
						// initialize
							if (!$eventHandlers) $eventHandlers = array();
							$eventHandlers['onChange'] = "this.value = this.value.replace(/,/g, " . '""' . "); if (isNaN(this.value)) { this.value = " . '"0.00"' . "; } " . @$eventHandlers['onChange'];
							if (!is_null(@$otherAttributes['max'])) $eventHandlers['onChange'] = "if (this.value && this.value > " . floatval($otherAttributes['max']) . ") { this.value = " . '"' . number_format($otherAttributes['max'], 2) . '"' . "; } " . @$eventHandlers['onChange'];
							if (!is_null(@$otherAttributes['min'])) $eventHandlers['onChange'] = "if (this.value && this.value < " . floatval($otherAttributes['min']) . ") { this.value = " . '"' . number_format($otherAttributes['min'], 2) . '"' . "; } " . @$eventHandlers['onChange'];
						// return
							$field .= "<input type='text' name='" . $name . "' id='" . $name . "'";
							if ($placeholder) $field .= " placeholder='" . htmlspecialchars($placeholder, ENT_QUOTES) . "'";
							$field .= " class='" . trim("input-mini " . $class) . "'";
							if ($value) $field .= " value='" . number_format($value, 2) . "'";
							else $field .= " value='0.00'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " />";
							return $field;
							break;
				/* ------------------------- */
					case 'dollars_bootstrap':
						// validate
							if (!$name) break; // no name
							$value = str_replace(',', '', $value);
							if ($value && !is_numeric($value)) break; // can't put a non-numeric value in a value field
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
						// initialize
							if (!$eventHandlers) $eventHandlers = array();
							$eventHandlers['onChange'] = "this.value = this.value.replace(/,/g, " . '""' . "); if (isNaN(this.value)) { this.value = " . '"0.00"' . "; } " . @$eventHandlers['onChange'];
							if (!is_null(@$otherAttributes['max'])) $eventHandlers['onChange'] = "if (this.value && this.value > " . floatval($otherAttributes['max']) . ") { this.value = " . '"' . number_format($otherAttributes['max'], 2) . '"' . "; } " . @$eventHandlers['onChange'];
							if (!is_null(@$otherAttributes['min'])) $eventHandlers['onChange'] = "if (this.value && this.value < " . floatval($otherAttributes['min']) . ") { this.value = " . '"' . number_format($otherAttributes['min'], 2) . '"' . "; } " . @$eventHandlers['onChange'];
						// return
							$field = "<div class='input-prepend'><span class='add-on'>$</span>";
							$field .= "<input type='text' name='" . $name . "' id='" . $name . "'";
							if ($placeholder) $field .= " placeholder='" . htmlspecialchars($placeholder, ENT_QUOTES) . "'";
							$field .= " class='" . trim("input-mini " . $class) . "'";
							if ($value) $field .= " value='" . number_format($value, 2) . "'";
							else $field .= " value='0.00'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " />";
							$field .= "</div>";
							return $field;
							break;
				/* ------------------------- */
					case 'pounds_bootstrap':
						// validate
							if (!$name) break; // no name
							$value = str_replace(',', '', $value);
							if ($value && !is_numeric($value)) break; // can't put a non-numeric value in a value field
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
						// initialize
							if (!$eventHandlers) $eventHandlers = array();
							$eventHandlers['onChange'] = "this.value = this.value.replace(/,/g, " . '""' . "); if (isNaN(this.value)) { this.value = " . '"0.00"' . "; } " . @$eventHandlers['onChange'];
							if (!is_null(@$otherAttributes['max'])) $eventHandlers['onChange'] = "if (this.value && this.value > " . floatval($otherAttributes['max']) . ") { this.value = " . '"' . number_format($otherAttributes['max'], 2) . '"' . "; } " . @$eventHandlers['onChange'];
							if (!is_null(@$otherAttributes['min'])) $eventHandlers['onChange'] = "if (this.value && this.value < " . floatval($otherAttributes['min']) . ") { this.value = " . '"' . number_format($otherAttributes['min'], 2) . '"' . "; } " . @$eventHandlers['onChange'];
						// return
							$field = "<div class='input-prepend'><span class='add-on'>&pound;</span>";
							$field .= "<input type='text' name='" . $name . "' id='" . $name . "'";
							if ($placeholder) $field .= " placeholder='" . htmlspecialchars($placeholder, ENT_QUOTES) . "'";
							$field .= " class='" . trim("input-mini " . $class) . "'";
							if ($value) $field .= " value='" . number_format($value, 2) . "'";
							else $field .= " value='0.00'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " />";
							$field .= "</div>";
							return $field;
							break;
				/* ------------------------- */
					case 'yen_bootstrap':
						// validate
							if (!$name) break; // no name
							$value = intval(str_replace(',', '', $value));
							if ($value && !is_numeric($value)) break; // can't put a non-numeric value in a value field
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
						// initialize
							if (!$eventHandlers) $eventHandlers = array();
							$eventHandlers['onChange'] = "this.value = this.value.replace(/,/g, " . '""' . "); if (isNaN(this.value)) { this.value = " . '"0.00"' . "; } " . @$eventHandlers['onChange'];
							if (!is_null(@$otherAttributes['max'])) $eventHandlers['onChange'] = "if (this.value && this.value > " . floatval($otherAttributes['max']) . ") { this.value = " . '"' . number_format($otherAttributes['max'], 2) . '"' . "; } " . @$eventHandlers['onChange'];
							if (!is_null(@$otherAttributes['min'])) $eventHandlers['onChange'] = "if (this.value && this.value < " . floatval($otherAttributes['min']) . ") { this.value = " . '"' . number_format($otherAttributes['min'], 2) . '"' . "; } " . @$eventHandlers['onChange'];
						// return
							$field = "<div class='input-prepend'><span class='add-on'>&yen;</span>";
							$field .= "<input type='text' name='" . $name . "' id='" . $name . "'";
							if ($placeholder) $field .= " placeholder='" . htmlspecialchars($placeholder, ENT_QUOTES) . "'";
							$field .= " class='" . trim("input-mini " . $class) . "'";
							if ($value) $field .= " value='" . floatval($value) . "'";
							else $field .= " value='0.00'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " />";
							$field .= "</div>";
							return $field;
							break;
				/* ------------------------- */
					case 'radio':
						// validate
							if (!$name) break; // no name
							if (count($options) < 1) break; // no options to display as radio buttons
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
						// initialize
							$field = '';
							$increment = 0;
						// return
							foreach ($options as $optionValue => $optionLabel) {
								$field .= "<input type='radio' name='" . $name . "' id='" . $name . "_" . $increment . "'";
								if ($class) $field .= " class='" . $class . "'";
								$field .= " value='" . $optionValue . "'";
								if ($value == $optionValue) $field .= " checked";
								if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
								if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
								if ($mandatory) $field .= " required";
								$field .= " /> " . $optionLabel;
								if ($increment < count($options) - 1) $field .= " &nbsp; ";
								$increment++;
							}
							return $field;
							break;
				/* ------------------------- */
					case 'radio_stacked_bootstrap':
						// validate
							if (!$name) break; // no name
							if (count($options) < 1) break; // no options to display as radio buttons
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
						// initialize
							$field = '';
							$increment = 0;
						// return
							foreach ($options as $optionValue => $optionLabel) {
								$field .= "<label class='radio'>";
								$field .= "<input type='radio' name='" . $name . "' id='" . $name . "_" . $increment . "'";
								if ($class) $field .= " class='" . $class . "'";
								$field .= " value='" . $optionValue . "'";
								if ($value == $optionValue) $field .= " checked";
								if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
								if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
								if ($mandatory) $field .= " required";
								$field .= " /> " . $optionLabel;
								$field .= "</label>";
							}
							return $field;
							break;
				/* ------------------------- */
					case 'checkbox':
						// validate
							if (!$name) break; // no name
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
						// initialize
							$field = '';
						// return
							$field .= "<input type='checkbox' name='" . $name . "' id='" . $name . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($value) $field .= " checked='checked'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " /> " . $label;
							return $field;
							break;
				/* ------------------------- */
					case 'checkbox_stacked_bootstrap':
						// validate
							if (!$name) break; // no name
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
						// initialize
							$field = '';
						// return
							$field .= "<label class='checkbox'>";
							$field .= "<input type='checkbox' name='" . $name . "' id='" . $name . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($value) $field .= " checked='checked'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " /> " . $label;
							$field .= "</label>";
							return $field;
							break;
				/* ------------------------- */
					case 'textarea':
						// validate
							if (!$name) break; // no name
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
						// return
							$field = "<textarea name='" . $name . "' id='" . $name . "'";
							if ($placeholder) $field .= " placeholder='" . htmlspecialchars($placeholder, ENT_QUOTES) . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= ">" . $value . "</textarea>";
							return $field;
							break;
				/* ------------------------- */
					case 'date':
						// validate
							if (!$name) break; // no name
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
						// initialize
							$dateRegex = "^[0-9]{4}-(((0[13578]|(10|12))-(0[1-9]|[1-2][0-9]|3[0-1]))|(02-(0[1-9]|[1-2][0-9]))|((0[469]|11)-(0[1-9]|[1-2][0-9]|30)))$";
						// return
							$field = "<input type='text' name='" . $name . "' id='" . $name . "' placeholder='YYYY-MM-DD' pattern='" . $dateRegex . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($value && $value != '0000-00-00') $field .= " value='" . $value . "' ";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " \>";
							return $field;
							break;
				/* ------------------------- */
					case 'hoursminutes':
						// validate
							if (!$name) break; // no name
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
						// initialize
							$dateRegex = "^((([0]?[1-9]|1[0-2])(:|\.)[0-5][0-9]((:|\.)[0-5][0-9])?( )?(AM|am|aM|Am|PM|pm|pM|Pm))|(([0]?[0-9]|1[0-9]|2[0-3])(:|\.)[0-5][0-9]((:|\.)[0-5][0-9])?))$";
						// return
							$field = "<input type='text' name='" . $name . "' id='" . $name . "' class='" . trim("input-small " . $class) . "' placeholder='HH:MM PM' pattern='" . $dateRegex . "'";
							if ($value) $field .= " value='" . $value . "' ";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " \>";
							return $field;
							break;
				/* ------------------------- */
					case 'year':
						// validate
							if (!$name) break; // no name
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
						// initialize
							$dateRegex = "^[0-9]{4}$";
							if (!$eventHandlers && (!is_null(@$otherAttributes['max']) || !is_null(@$otherAttributes['min']))) $eventHandlers = array();
							if (!is_null(@$otherAttributes['max'])) $eventHandlers['onChange'] = "if (this.value && this.value > " . floatval($otherAttributes['max']) . ") this.value = " . intval($otherAttributes['max']) . "; " . @$eventHandlers['onChange'];
							if (!is_null(@$otherAttributes['min'])) $eventHandlers['onChange'] = "if (this.value && this.value < " . floatval($otherAttributes['min']) . ") this.value = " . intval($otherAttributes['min']) . "; " . @$eventHandlers['onChange'];
						// return
							$field = "<input type='number' name='" . $name . "' id='" . $name . "' class='input-mini' placeholder='YYYY' pattern='" . $dateRegex . "' maxlength='4'";
							if (floatval($value)) $field .= " value='" . floatval($value) . "' ";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " \>";
							return $field;
							break;
				/* ------------------------- */
					case 'file':
						// validate
							if (!$name) break; // no name
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
						// return
							$field = "<input type='file' name='" . $name . "' id='" . $name . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " />";
							return $field;
							break;
				/* ------------------------- */
					case 'range':
						// validate
							if (!$name) break; // no name
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
							if (!is_null(@$otherAttributes['max'])) break; // no upper ceiling on range
						// return
							$field = "<input type='range' name='" . $name . "' id='" . $name . "' max='" . $max . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= " />";
							return $field;
							break;
				/* ------------------------- */
					case 'country':
						// validate
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
							$countries = retrieveFromDb('countries', null, null, null, null, null, 'country ASC');
						// initialize
							if (!$name) $name = 'country';
						// return
							$field = "<select name='" . $name . "' id='" . $name . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= ">";
							if ($label && !$mandatory) {
								$field .= "<option value=''>" . $label . "</option>";
								$field .= "<option value=''>--</option>";
							}
							for ($counter = 0; $counter < count($countries); $counter++) {
								$field .= "  <option value='" . $countries[$counter]['country_id'] . "'";
								if ($value && $countries[$counter]['country_id'] == $value) $field .= " selected";
								$field .= ">";
								if ($truncateLabel > strlen($countries[$counter]['country']) - 3) $countries[$counter]['country'] = substr($countries[$counter]['country'], 0, $truncateLabel) . '...';
								$field .= $countries[$counter]['country'];
								$field .= "</option>";
							}
							$field .= "</select>";
							return $field;
							break;
				/* ------------------------- */
					case 'state':
						// validate
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
							$states = retrieveFromDb('states_in_usa', null, null, null, null, null, 'state ASC');
						// initialize
							if (!$name) $name = 'state';
						// return
							$field = "<select name='" . $name . "' id='" . $name . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= ">";
							if ($label && !$mandatory) {
								$field .= "<option value=''>" . $label . "</option>";
								$field .= "<option value=''>--</option>";
							}
							for ($counter = 0; $counter < count($states); $counter++) {
								$field .= "  <option value='" . $states[$counter]['state_id'] . "'";
								if ($value && $states[$counter]['state_id'] == $value) $field .= " selected";
								$field .= ">";
								if ($truncateLabel > strlen($states[$counter]['state']) - 3) $states[$counter]['state'] = substr($states[$counter]['state'], 0, $truncateLabel) . '...';
								$field .= $states[$counter]['state'];
								$field .= "</option>";
							}
							$field .= "</select>";
							return $field;
							break;
				/* ------------------------- */
					case 'province':
						// validate
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
							$provinces = retrieveFromDb('provinces_in_canada', null, null, null, null, null, 'province ASC');
						// initialize
							if (!$name) $name = 'province';
						// return
							$field = "<select name='" . $name . "' id='" . $name . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= ">";
							if ($label && !$mandatory) {
								$field .= "<option value=''>" . $label . "</option>";
								$field .= "<option value=''>--</option>";
							}
							for ($counter = 0; $counter < count($provinces); $counter++) {
								$field .= "  <option value='" . $provinces[$counter]['province_id'] . "'";
								if ($value && $provinces[$counter]['province_id'] == $value) $field .= " selected";
								$field .= ">";
								if ($truncateLabel > strlen($provinces[$counter]['province']) - 3) $provinces[$counter]['province'] = substr($provinces[$counter]['province'], 0, $truncateLabel) . '...';
								$field .= $provinces[$counter]['province'];
								$field .= "</option>";
							}
							$field .= "</select>";
							return $field;
							break;
				/* ------------------------- */
					case 'stateProvince':
						// validate
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
							$states = retrieveFromDb('states_in_usa', null, null, null, null, null, 'state ASC');
							$provinces = retrieveFromDb('provinces_in_canada', null, null, null, null, null, 'province ASC');
						// initialize
							if (!$name) $name = 'stateProvince';
						// return
							$field = "<select name='" . $name . "' id='" . $name . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= ">";
							if ($label && !$mandatory) {
								$field .= "<option value=''>" . $label . "</option>";
								$field .= "<option value=''>--</option>";
							}
							for ($counter = 0; $counter < count($states); $counter++) {
								$field .= "  <option value='" . $states[$counter]['state_id'] . "'";
								if ($value && $states[$counter]['state_id'] == $value) $field .= " selected";
								$field .= ">";
								if ($truncateLabel > strlen($states[$counter]['state']) - 3) $states[$counter]['state'] = substr($states[$counter]['state'], 0, $truncateLabel) . '...';
								$field .= $states[$counter]['state'];
								$field .= "</option>";
							}
							$field .= "<option value=''>--</option>";
							for ($counter = 0; $counter < count($provinces); $counter++) {
								$field .= "  <option value='" . $provinces[$counter]['province_id'] . "'";
								if ($value && $provinces[$counter]['province_id'] == $value) $field .= " selected";
								$field .= ">";
								if ($truncateLabel > strlen($provinces[$counter]['province']) - 3) $provinces[$counter]['province'] = substr($provinces[$counter]['province'], 0, $truncateLabel) . '...';
								$field .= $provinces[$counter]['province'];
								$field .= "</option>";
							}
							$field .= "</select>";
							return $field;
							break;
				/* ------------------------- */
					case 'provinceState':
						// validate
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
							$states = retrieveFromDb('states_in_usa', null, null, null, null, null, 'state ASC');
							$provinces = retrieveFromDb('provinces_in_canada', null, null, null, null, null, 'province ASC');
						// initialize
							if (!$name) $name = 'provinceState';
						// return
							$field = "<select name='" . $name . "' id='" . $name . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= ">";
							if ($label && !$mandatory) {
								$field .= "<option value=''>" . $label . "</option>";
								$field .= "<option value=''>--</option>";
							}
							for ($counter = 0; $counter < count($provinces); $counter++) {
								$field .= "  <option value='" . $provinces[$counter]['province_id'] . "'";
								if ($value && $provinces[$counter]['province_id'] == $value) $field .= " selected";
								$field .= ">";
								if ($truncateLabel > strlen($provinces[$counter]['province']) - 3) $provinces[$counter]['province'] = substr($provinces[$counter]['province'], 0, $truncateLabel) . '...';
								$field .= $provinces[$counter]['province'];
								$field .= "</option>";
							}
							$field .= "<option value=''>--</option>";
							for ($counter = 0; $counter < count($states); $counter++) {
								$field .= "  <option value='" . $states[$counter]['state_id'] . "'";
								if ($value && $states[$counter]['state_id'] == $value) $field .= " selected";
								$field .= ">";
								if ($truncateLabel > strlen($states[$counter]['state']) - 3) $states[$counter]['state'] = substr($states[$counter]['state'], 0, $truncateLabel) . '...';
								$field .= $states[$counter]['state'];
								$field .= "</option>";
							}
							$field .= "</select>";
							return $field;
							break;
				/* ------------------------- */
					case 'language':
						// validate
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
							$languages = retrieveFromDb('languages', array('common'=>'1'), null, null, null, null, 'language ASC');
						// initialize
							if (!$name) $name = 'language';
						// return
							$field = "<select name='" . $name . "' id='" . $name . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							if ($mandatory) $field .= " required";
							$field .= ">";
							if ($label && !$mandatory) {
								$field .= "<option value=''>" . $label . "</option>";
								$field .= "<option value=''>--</option>";
							}
							for ($counter = 0; $counter < count($languages); $counter++) {
								$field .= "  <option value='" . $languages[$counter]['language_id'] . "'";
								if ($value && $languages[$counter]['language_id'] == $value) $field .= " selected";
								$field .= ">";
								if ($truncateLabel > strlen($languages[$counter]['language']) - 3) $languages[$counter]['language'] = substr($languages[$counter]['language'], 0, $truncateLabel) . '...';
								$field .= $languages[$counter]['language'];
								$field .= "</option>";
							}
							$field .= "</select>";
							return $field;
							break;
				/* ------------------------- */
					case 'multipicker':
						// validate
							if (!$name) break; // no name
							if (count($options) < 1) break; // no options to display in multipicker
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
						// initialize
							$valueDelimiter = ';';
							$increment = 0;
						// return
							$field = "<div style='padding: 4px; border: 1px solid #ddd; overflow: scroll; overflow-x: hidden;'";
							if ($class) $field .= " class='" . $class . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							$field .= ">";
							foreach ($options as $optionValue => $optionLabel) {
								if (substr_count($value, $optionValue)) {
									$field .= "<a href='javascript:void(0)' id='multipickerLink_" . $increment . "' style='font-weight: bold; text-decoration: none;' onClick='if (document.getElementById(" . '"multipickerLink_' . $increment . '"' . ").style.fontWeight == " . '"normal"' . ") { document.getElementById(" . '"multipickerLink_' . $increment . '"' . ").style.fontWeight = " . '"bold"' . "; } else { document.getElementById(" . '"multipickerLink_' . $increment . '"' . ").style.fontWeight = " . '"normal"' . "; } if (document.getElementById(" . '"' . $name . '"' . ").value.indexOf(" . '"' . $optionValue . '"' . ") > -1) { document.getElementById(" . '"' . $name . '"' . ").value = document.getElementById(" . '"' . $name . '"' . ").value.replace(" . '"' . $valueDelimiter . $optionValue . '"' . ", " . '""' . "); document.getElementById(" . '"' . $name . '"' . ").value = document.getElementById(" . '"' . $name . '"' . ").value.replace(" . '"' . $optionValue . '"' . ", " . '""' . "); } else { if (!document.getElementById(" . '"' . $name . '"' . ").value) { document.getElementById(" . '"' . $name . '"' . ").value = " . '"' . $optionValue . '"' . "; } else { document.getElementById(" . '"' . $name . '"' . ").value += " . '"' . $valueDelimiter . $optionValue . '"' . "; } } return false;'>";
									$field .= $optionLabel;
									$field .= "</a> &nbsp; ";
								}
								else {
									$field .= "<a href='javascript:void(0)' id='multipickerLink_" . $increment . "' style='font-weight: normal; text-decoration: none;' onClick='if (document.getElementById(" . '"multipickerLink_' . $increment . '"' . ").style.fontWeight == " . '"normal"' . ") { document.getElementById(" . '"multipickerLink_' . $increment . '"' . ").style.fontWeight = " . '"bold"' . "; } else { document.getElementById(" . '"multipickerLink_' . $increment . '"' . ").style.fontWeight = " . '"normal"' . "; } if (document.getElementById(" . '"' . $name . '"' . ").value.indexOf(" . '"' . $optionValue . '"' . ") > -1) { document.getElementById(" . '"' . $name . '"' . ").value = document.getElementById(" . '"' . $name . '"' . ").value.replace(" . '"' . $valueDelimiter . $optionValue . '"' . ", " . '""' . "); document.getElementById(" . '"' . $name . '"' . ").value = document.getElementById(" . '"' . $name . '"' . ").value.replace(" . '"' . $optionValue . '"' . ", " . '""' . "); } else { if (!document.getElementById(" . '"' . $name . '"' . ").value) { document.getElementById(" . '"' . $name . '"' . ").value = " . '"' . $optionValue . '"' . "; } else { document.getElementById(" . '"' . $name . '"' . ").value += " . '"' . $valueDelimiter . $optionValue . '"' . "; } } return false;'>";
									$field .= $optionLabel;
									$field .= "</a> &nbsp; ";
								}
								$increment++;
							}
							$field .= "</div>";
							$field .= "<input type='hidden' name='" . $name . "' id='" . $name . "' value='" . $value . "' />";
							return $field;
							break;
				/* ------------------------- */
					case 'button':
					case 'submit':
						// validate
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
						// initialize
							if (!$label && $type == 'button') $label = 'Go';
							if (!$label && $type == 'submit') $label = 'Submit';
						// return
							$field = "<input type='" . $type . "'";
							$field .= " value='" . htmlspecialchars($label, ENT_QUOTES) . "'";
							if ($name) $field .= " name='" . $name . "' id='" . $name . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							$field .= " />";
							return $field;
							break;
				/* ------------------------- */
					case 'image':
						// validate
							if (!$otherAttributes['src']) break; // no image
							if ($otherAttributes && !is_array($otherAttributes)) return false; // invalid array
							if ($eventHandlers && !is_array($eventHandlers)) return false; // invalid array
						// return
							$field = "<input type='image'";
							$field .= " src='" . $otherAttributes['src'] . "'";
							if ($name) $field .= " name='" . $name . "' id='" . $name . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							$field .= " />";
							return $field;
							break;
				/* ------------------------- */
					case 'cancel_and_return':
						// initialize
							if (!$label) $label = 'Cancel';
						// return
							$field = "<input type='button'";
							if ($name) $field .= " name='" . $name . "' id='" . $name . "'";
							$field .= " value='" . htmlspecialchars($label, ENT_QUOTES) . "'";
							if ($class) $field .= " class='" . $class . "'";
							if ($otherAttributes && is_array($otherAttributes)) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
							@$eventHandlers['onClick'] .= "window.history.back(); return false;";
							foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
							$field .= " />";
							return $field;
							break;
			}
			
		}
	
		public function row($type, $name, $value = null, $mandatory = false, $labelPlaceholder = null, $class = null, $options = null, $maxlength = null, $otherAttributes = null, $truncateLabel = null, $eventHandlers = null, $duplicateRows = null) {

			if (!$type) {
				errorManager_TL::addError("No element type specified.");
				return false;
			}
			
			if ($labelPlaceholder) {
				$result = explode('|', $labelPlaceholder);
				if (isset($result[0])) $label = $result[0];
				if (isset($result[1])) $placeholder = $result[1];
			}
			
			if (!isset($label)) $label = null;
			if (!isset($placeholder)) $placeholder = null;
			
			if (floatval($truncateLabel)) $label = truncateString_TL($label, 'character', $truncateLabel, '', '', '');
			
			if ($type == 'checkbox' || $type == 'checkbox_stacked_bootstrap' || $type == 'button' || $type == 'submit' || $type == 'cancel' || $type == 'cancel_and_return') $label = null;
			
			$row = "<!-- " . $label . " -->\n";
			$row .= "  <div id='formLabel_" . $name . "' class='formLabel'>" . $label . "</div>\n";
			$row .= "  <div id='formField_" . $name . "' class='formField'>\n";
			$row .= "    " . $this->input($type, $name, $value, $mandatory, $labelPlaceholder, $class, $options, $maxlength, $otherAttributes, $truncateLabel, $eventHandlers) . "\n";
			
			if ($duplicateRows) {
				for ($counter = 1; $counter <= $duplicateRows; $counter++) {
					$row .= "    <div id='expandingRow_" . $counter . "'></div>\n";
				}
				
				$row .= "    <div id='rowLink'>\n";
				$row .= "      <a href='javascript:void(0)' onClick='addRow(" . $duplicateRows . "); return false;'>Add more...</a>\n";
				$row .= "      <input type='hidden' name='numberOfRows' id='numberOfRows' value='0' />\n";
				$row .= "    </div>\n";
				
				$row .= "    <script type='text/javascript'>\n";
				$row .= "      function addRow(maxRows) {\n";
				$row .= "        document.getElementById('numberOfRows').value++;\n";
				$row .= "        if (document.getElementById('numberOfRows').value > maxRows) alert('Maximum number of rows reached.');\n";
				$row .= "        else {\n";
				$row .= "          var field =\n";
				$row .= "            " . '"' . $this->input($type, $name, $value, $mandatory, $labelPlaceholder, $class, $options, $maxlength, $otherAttributes, $truncateLabel, $eventHandlers) . '"' . ";\n";
				$row .= "          document.getElementById('expandingRow_' + document.getElementById('numberOfRows').value).innerHTML = field.replace(" . '"' . $name . '", "' . $name . '"' . " + " . '"_"' . " + document.getElementById('numberOfRows').value);\n";
				$row .= "        }\n";
				$row .= "      }\n";
				$row .= "    </script>\n";
	
			}
			
			$row .= "  </div>\n";
			$row .= "  <div class='floatClear'></div>\n";
			
			return $row;
			
		}

		public function rowStart($name = null, $label = null, $truncate = false) {

			if (!isset($name)) $name = null;
			if (!isset($label)) $label = null;
			
			if (floatval($truncate)) $label = truncateString_TL($label, 'character', $truncate, '', '', '');
			
			$row = "<!-- " . $label . " -->\n";
			$row .= "  <div id='" . trim('formLabel_' . $name, '_') . "' class='formLabel'>" . $label . "</div>\n";
			$row .= "  <div id='" . trim('formField_' . $name, '_') . "' class='formField'>\n";

			return $row;
			
		}
			
		public function rowEnd() {
		
			$row = "  </div>\n";
			$row .= "  <div class='floatClear'></div>\n";
			
			return $row;
			
		}
		
		public function start($name = null, $action = null, $method = 'post', $class = null, $otherAttributes = null, $eventHandlers = null) {
			
			$field = "<form";
			if ($name) $field .= " name='" . $name . "' id='" . $name . "'";
			if ($action) $field .= " action='" . $action . "'";
			if ($method) $field .= " method='" . $method . "'";
			if ($class) $field .= " class='" . $class . "'";
			if ($otherAttributes) foreach ($otherAttributes as $attribute => $attributeValue) $field .= " " . $attribute . "='" . trim($attributeValue) . "'";
			if ($eventHandlers) foreach ($eventHandlers as $event => $action) $field .= " " . $event . "='" . trim($action) . "'";
			$field .= ">";
			if ($name) $field .= "<input type='hidden' name='formName' id='formName' value='" . $name . "' />";
			
			return $field;
			
		}
		
		public function end() {
			return "</form>";
		}
		
		public function paginate($currentPage, $numberOfPages, $urlStructure) {

			/* 	Note that URL structure must contain a # where the
				page number will appear */
			
			$paginate = "<div class='pagination'>\n";
			$paginate .= "  " . $this->start('paginationForm', '', 'post', 'form-inline') . "\n";
			// back
				if ($currentPage > 1) $eventHandler = array('onClick'=>'document.location.href="' . str_replace('#', $currentPage - 1, $urlStructure) . '"; return false;');
				else $eventHandler = null;
				$paginate .= "  <div class='form-group'>" . $this->input('button', 'paginateButtonBack', null, false, '<', 'btn btn-default', null, null, null, null, @$eventHandler) . "</div>\n";
			// select
				$allPages = array();
				for ($counter = 1; $counter <= $numberOfPages; $counter++) {
					$allPages[$counter] = $counter;
				}
				$paginate .= "  <div class='form-group'>" . $this->input('select', 'selectPage', $currentPage, false, null, 'form-control', $allPages, null, null, null, array('onChange'=>'document.location.href="' . str_replace('#', '" + this.value + "', $urlStructure) . '"; return false;')) . "</div>\n";
			// next
				if ($currentPage < $numberOfPages) $eventHandler = array('onClick'=>'document.location.href="' . str_replace('#', $currentPage + 1, $urlStructure) . '"; return false;');
				else $eventHandler = null;
				$paginate .= "  <div class='form-group'>" . $this->input('button', 'paginateButtonBack', null, false, '>', 'btn btn-default', null, null, null, null, @$eventHandler) . "</div>\n";
			$paginate .= "  " . $this->end() . "\n";
			$paginate .= "</div>\n";
			
			return $paginate;
			
		}
		
	}
	
/*
	Form Builder

	::	DESCRIPTION
	
		Creates form fields and, if necessary, enclosing CSS scaffording to
		optimize form creation.

	::	DEPENDENT ON
	
	::	VERSION HISTORY

	::	LICENSE
	
		Copyright (C) Timothy Quinn / Tidal Lock / Consolidated Biro
		
		Permission is hereby granted, free of charge, to any person
		obtaining a copy of this software and associated documentation
		files (the "Software"), to deal in the Software without
		restriction, including without limitation the rights to use,
		copy, modify, merge, publish, distribute, sublicense, and/or
		sell copies of the Software, and to permit persons to whom the
		Software is furnished to do so, subject to the following
		conditions:
		
		The above copyright notice and this permission notice shall be
		included in all copies or substantial portions of the Software.
		THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
		EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
		OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
		NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
		HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
		WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
		FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
		OTHER DEALINGS IN THE SOFTWARE.
*/

?>
