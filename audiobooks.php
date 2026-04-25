<?php
$pageTitle = 'Wellness Library & Media';
require_once 'includes/config.php';
require_once 'includes/session.php';
require_once 'includes/functions.php';
requireLogin();

require_once 'includes/header.php';
?>

<div class="content-wrapper">
<div class="container" style="padding-top:40px; padding-bottom:60px;">
    
    <!-- Header Section -->
    <div style="text-align:center; margin-bottom:48px;" data-aos="fade-down">
        <h2 style="font-size:36px; font-weight:800; color:var(--text-primary);"><i class="fa-solid fa-headphones-simple" style="color:var(--color-primary);"></i> Wellness Media & Library</h2>
        <p style="color:var(--text-secondary); font-size:16px; margin-top:8px;">Curated YouTube music and video for focus, sleep, and mindfulness</p>
    </div>

    <!-- Media Sections -->
    <div style="display:flex; flex-direction:column; gap:60px;">
        
        <!-- Soothing Music Section -->
        <section>
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:24px;">
                <div style="width:40px; height:40px; border-radius:12px; background:var(--color-primary-light); display:flex; align-items:center; justify-content:center; color:var(--color-primary);">
                    <i class="fa-solid fa-music"></i>
                </div>
                <h3 style="font-size:24px; font-weight:800;">Soothing Music & Lo-fi</h3>
            </div>
            
            <div class="grid grid-3">
                <?php
                $musicContent = [
                    ['id' => 'jfKfPfyJRdk', 'title' => 'Lofi Hip Hop Radio', 'tag' => 'Study/Focus'],
                    ['id' => '5qap5aO4i9A', 'title' => 'Lofi Girl - Sleep Beats', 'tag' => 'Relaxation'],
                    ['id' => 'DWcUYEY6Wp4', 'title' => 'Calm Piano Music', 'tag' => 'Meditation']
                ];
                foreach ($musicContent as $item):
                ?>
                <div class="card" style="padding:0; overflow:hidden; border-radius:24px; border:1px solid var(--border-light);" data-aos="fade-up">
                    <div style="position:relative; padding-bottom:56.25%; height:0;">
                        <iframe style="position:absolute; top:0; left:0; width:100%; height:100%; border:0;" 
                            src="https://www.youtube.com/embed/<?= $item['id'] ?>" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                    <div style="padding:20px;">
                        <span style="background:var(--color-primary-light); color:var(--color-primary); font-size:11px; font-weight:800; padding:4px 10px; border-radius:12px; text-transform:uppercase;"><?= $item['tag'] ?></span>
                        <h4 style="margin-top:10px; font-size:17px; font-weight:700;"><?= $item['title'] ?></h4>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Guided Wellness Videos Section -->
        <section>
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:24px;">
                <div style="width:40px; height:40px; border-radius:12px; background:var(--color-secondary-light); display:flex; align-items:center; justify-content:center; color:var(--color-secondary-dark);">
                    <i class="fa-solid fa-person-rays"></i>
                </div>
                <h3 style="font-size:24px; font-weight:800;">Guided Wellness & Yoga</h3>
            </div>

            <div class="grid grid-3">
                <?php
                $videoContent = [
                    ['id' => 'v7AYKMP6rOE', 'title' => '10 Min Morning Yoga', 'tag' => 'Yoga'],
                    ['id' => 'inpok4MKVLM', 'title' => '5 Min Guided Meditation', 'tag' => 'Mindfulness'],
                    ['id' => 's98U9O69D0I', 'title' => 'Cycle Syncing 101', 'tag' => 'Education']
                ];
                foreach ($videoContent as $item):
                ?>
                <div class="card" style="padding:0; overflow:hidden; border-radius:24px; border:1px solid var(--border-light);" data-aos="fade-up">
                    <div style="position:relative; padding-bottom:56.25%; height:0;">
                        <iframe style="position:absolute; top:0; left:0; width:100%; height:100%; border:0;" 
                            src="https://www.youtube.com/embed/<?= $item['id'] ?>" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                    <div style="padding:20px;">
                        <span style="background:var(--color-secondary-light); color:var(--color-secondary-dark); font-size:11px; font-weight:800; padding:4px 10px; border-radius:12px; text-transform:uppercase;"><?= $item['tag'] ?></span>
                        <h4 style="margin-top:10px; font-size:17px; font-weight:700;"><?= $item['title'] ?></h4>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

    </div>
</div>
</div>

<style>
.content-wrapper {
    background: var(--bg-body);
    min-height: 100vh;
}
.card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg);
    border-color: var(--color-primary);
}
</style>

<?php require_once 'includes/footer.php'; ?>
