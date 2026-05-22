<div id="cosy-appointments">

    <h2><?php esc_html_e('Book Your Appointment', 'cosy-appointments'); ?></h2>
    <div id="cosy-slots"><?php esc_html_e('Loading slots...', 'cosy-appointments'); ?></div>
    <form id="cosy-book-form">
        <?php wp_nonce_field('cosy_book_appointment', 'cosy_nonce'); ?>
        <input type="text" name="fname" placeholder="<?php esc_attr_e('Your Name', 'cosy-appointments'); ?>" required>
        <input type="hidden" name="slot" id="cosy-slot-input" /><br /><br />
        <button type="submit"><?php esc_html_e('Book Slot', 'cosy-appointments'); ?></button>
    </form>
</div>