<!DOCTYPE html>
<html lang="en">

<?php 
// Provide default values if variables are not defined
Flight::render('partials/header', [
    'title' => $title ?? 'Tag Denton',
    'description' => $description ?? 'Explore Denton landmarks with Tag Denton',
    'keywords' => $keywords ?? 'Denton, landmarks, Tag Denton',
    'image' => $image ?? '/images/default-thumbnail.jpg'
]); 
?>

<body class="bg-light">
    <?php Flight::render('partials/navbar'); ?>

    <div class="container mt-4">
        <?php echo $content ?>
    </div>

    <?php Flight::render('partials/footer'); ?>
</body>

</html>
