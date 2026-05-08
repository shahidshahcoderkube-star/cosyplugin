<div id="cosy-appointments">

    <h2>Book Your Appointment</h2>
    <div id="cosy-slots">Loading slots...</div>
    <form id="cosy-book-form">
        <?php wp_nonce_field('cosy_book_appointment', 'cosy_nonce'); ?>
        <input type="text" name="fname" placeholder="Your Name" required>
        <input type="hidden" name="slot" id="cosy-slot-input" /><br /><br />
        <button type="submit">Book Slot</button>
    </form>
</div>