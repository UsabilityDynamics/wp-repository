<?php
/**
 * Composer Repository Updater view
 */
?>
<div class="updater">
  <div class="desc">
    <span style="display:inline-block;line-height:28px;padding-right:6px;"><?php _e( 'Update Composer Repository Files' ); ?></span>
    <button id="wpr_check_updates_for_repository" class="button-primary" ><?php _e( 'Check Updates' ); ?></button>
    <img id="wpr_spinner" style="display:none;padding-left:10px;" src="<?php echo $this->path( 'static/images/ajax-loader.gif' ); ?>" />
    <p class="description"><?php _e( 'Files will be saved in defined Composer Repository directory.' ); ?></p>
  </div>
</div>