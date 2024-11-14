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

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
</body>

</html>
