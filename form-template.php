<form id="elegant-contact-form">
    <div>
        <label for="name">Name (required)</label>
        <input type="text" id="name" name="name" required>
    </div>
    <div>
        <label for="email">Email (required)</label>
        <input type="email" id="email" name="email" required>
    </div>
    <div>
        <label for="phone">Phone (optional)</label>
        <input type="tel" id="phone" name="phone">
    </div>
    <div>
        <label for="message">Message</label>
        <textarea id="message" name="message" required></textarea>
    </div>
    <div class="g-recaptcha" data-sitekey="<?php echo esc_attr(get_option('ecf_recaptcha_site_key')); ?>"></div>
    <button type="submit">Submit</button>
</form>

<script>
document.getElementById('elegant-contact-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    var formData = new FormData(this);
    
    fetch('/wp-json/elegant-contact-form/v1/submit', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            alert(data.message);
            this.reset();
            grecaptcha.reset();
        } else {
            alert('An error occurred. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
});
</script>