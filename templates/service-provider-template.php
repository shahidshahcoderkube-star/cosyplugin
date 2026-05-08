<?php

$providers = $this->get_all_service_providers();

if (empty($providers)) {
    echo '<p>No service providers found.</p>';
    return;
}

?>
<div class="services-grid mb-5 mt-5">

    <?php foreach ($providers as $provider):

        // echo '<pre>';
        // print_r($provider);
        // echo '</pre>';
    

        ?>
        <div class="service-card">
            <div class="profile-area">
                <img src="https://i.pravatar.cc/200?u=sarah" class="profile-pic" alt="Sarah">
            </div>

            <h3 class="name"><?php echo $provider['first_name'] . ' ' . $provider['last_name']; ?> </h3>
            <div class="rating">
                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                    class="fas fa-star"></i><i class="fas fa-star-half-alt"></i> <span>(4.8)</span>
            </div>

            <p class="bio">
                <?php echo $provider['description']; ?>
            </p>

            <div class="card-footer">
                <div class="price-tag">$45 <span>/ hr</span></div>
                <div class="button-group">
                    <button class="btn btn-video" onclick="openVideo('https://www.youtube.com/embed/ScMzIvxBSi4')">
                        <i class="fas fa-play-circle"></i> Intro
                    </button>
                    <a href="<?php echo get_author_posts_url($provider['ID']); ?>" class="btn btn-profile">View Profile</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

</div>

<div id="videoModal" class="modal" onclick="closeVideo()">
    <div class="modal-content" onclick="event.stopPropagation()">
        <span class="close-modal" onclick="closeVideo()">&times;</span>
        <iframe id="videoFrame" width="100%" height="100%" src="" frameborder="0" allowfullscreen></iframe>
    </div>
</div>

<script>
    function openVideo(url) {
        document.getElementById('videoFrame').src = url;
        document.getElementById('videoModal').style.display = 'flex';
    }

    function closeVideo() {
        document.getElementById('videoModal').style.display = 'none';
        document.getElementById('videoFrame').src = '';
    }
</script>

</body>

</html>