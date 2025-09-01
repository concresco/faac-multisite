<input type="text" id="fl-{{data.name}}-input" name="{{data.name}}" class="text text-full" value="{{data.value}}" readonly />
<br /><br />
<span class="fl-field-description"><?php esc_html_e('Configure this module using the Module Builder or select an existing module.', 'wpcloudplugins'); ?></span>
<br /><br />
<button id="fl-{{data.name}}-select" class="fl-builder-button fl-builder-button" href="javascript:void(0);" onclick="return false;"><?php esc_html_e('Configure Module', 'wpcloudplugins'); ?></button>