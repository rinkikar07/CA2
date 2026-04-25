<?php
$pageTitle = 'Wellness Hub';
require_once 'includes/config.php';
require_once 'includes/session.php';
require_once 'includes/functions.php';
requireLogin();

$userId = getCurrentUserId();
$cycleSettings = getUserCycleSettings($userId);
$currentPhase = getCurrentPhase($cycleSettings['last_period_start'], $cycleSettings['avg_cycle_length'], $cycleSettings['avg_period_length']);
$phaseInfo = getPhaseInfo($currentPhase);

// Get phase-specific content
$stmt = $pdo->prepare("SELECT wc.*, cc.name as category_name, cc.icon as category_icon FROM wellness_content wc JOIN content_categories cc ON wc.category_id = cc.id WHERE wc.is_active = 1 AND (wc.target_phase = ? OR wc.target_phase = 'all') ORDER BY wc.content_type, RAND()");
$stmt->execute([$currentPhase]);
$content = $stmt->fetchAll();

$tips = array_filter($content, fn($c) => $c['content_type'] === 'tip');
$affirmations = array_filter($content, fn($c) => $c['content_type'] === 'affirmation');
$articles = array_filter($content, fn($c) => in_array($c['content_type'], ['article', 'book', 'audiobook']));

// Phase-specific text vibe (no emojis)
$phaseVibe = [
    'menstrual' => ['text' => 'Cozy vibes only'],
    'follicular' => ['text' => 'Blooming energy'],
    'ovulation' => ['text' => 'Radiant glow'],
    'luteal' => ['text' => 'Gentle unwinding']
];
$vibe = $phaseVibe[$currentPhase] ?? ['text' => 'Nurture yourself'];

require_once 'includes/header.php';
?>

<div class="content-wrapper">
<div class="container" style="padding-top:40px; padding-bottom:60px;">
    <div class="section-header" style="text-align:center; margin-bottom:32px;">
        <div class="text-reveal-wrapper">
            <h2 class="text-reveal" style="font-size:36px; text-shadow: 0 2px 10px rgba(0,0,0,0.05);"><i class="fa-solid fa-spa" style="color:var(--color-sage);"></i> Wellness Hub</h2>
        </div><br>
        <div class="text-reveal-wrapper" style="margin-top:8px;">
            <p class="text-reveal" style="color:var(--text-secondary); font-size:18px; animation-delay:0.2s;">
                Embrace your <span style="color:<?= $phaseInfo['color'] ?>; font-weight:700; background:var(--color-primary-light); padding:2px 10px; border-radius:12px;"><?= $phaseInfo['name'] ?></span>. <?= $vibe['text'] ?>
            </p>
        </div>
    </div>
    
    <!-- Affirmation Banner -->
    <?php $aff = reset($affirmations); if ($aff): ?>
    <div class="card" style="background:var(--bg-card); backdrop-filter:blur(10px); border:1px solid var(--border-light); box-shadow:var(--shadow-md); margin-bottom:30px; text-align:center; padding:40px; position:relative; overflow:hidden;" data-aos="zoom-in-up">
        <p style="font-size:14px; font-weight:800; text-transform:uppercase; letter-spacing:2px; color:var(--color-primary); margin-bottom:12px;">Daily Affirmation</p>
        <h3 style="font-size:26px; margin-bottom:12px; font-weight:800; color:var(--text-primary);"><?= sanitize($aff['title']) ?></h3>
        <p style="color:var(--text-secondary); max-width:600px; margin:0 auto; font-size:16px; line-height:1.6;"><?= sanitize($aff['body']) ?></p>
    </div>
    <?php endif; ?>
    
    <!-- Tips Grid -->
    <div style="text-align:center;">
        <div class="text-reveal-wrapper mb-3">
            <h3 class="text-reveal" style="font-size:22px;">Nurturing Tips for Your <?= $phaseInfo['name'] ?></h3>
        </div>
    </div>
    <div class="grid grid-3 mb-4">
        <?php foreach ($tips as $i => $tip): ?>
        <div class="card" style="background:var(--bg-card); backdrop-filter:blur(10px); border:1px solid var(--border-light); text-align:center;" data-aos="zoom-in-up" data-aos-delay="<?= ($i % 3) * 100 ?>">
            <div style="display:flex; flex-direction:column; align-items:center; gap:10px; margin-bottom:12px;">
                <div style="width:40px; height:40px; border-radius:12px; background:var(--color-primary-light); display:flex; align-items:center; justify-content:center; color:var(--color-primary);">
                    <i class="fa-solid <?= $tip['category_icon'] ?>"></i>
                </div>
                <div>
                    <span style="font-size:12px; color:var(--text-muted); text-transform:uppercase;"><?= sanitize($tip['category_name']) ?></span>
                </div>
            </div>
            <h4 style="margin-bottom:8px; font-size:17px;"><?= sanitize($tip['title']) ?></h4>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6;"><?= sanitize($tip['body']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Curated Article Library -->
    <div style="width:100%; margin-bottom:40px;">
        <div style="text-align:center; margin-bottom:28px;" data-aos="zoom-in-up">
            <h3 style="font-size:26px; font-weight:800;">Wellness Library</h3>
            <p style="color:var(--text-muted); margin-top:6px;">Curated reads for every phase of your cycle</p>
        </div>

        <!-- Filter Tabs -->
        <div style="display:flex; gap:10px; flex-wrap:wrap; justify-content:center; margin-bottom:28px;" id="articleFilters">
            <button class="art-filter active" data-cat="all" onclick="filterArticles('all')">All</button>
            <button class="art-filter" data-cat="cycle" onclick="filterArticles('cycle')">Cycle</button>
            <button class="art-filter" data-cat="nutrition" onclick="filterArticles('nutrition')">Nutrition</button>
            <button class="art-filter" data-cat="mental" onclick="filterArticles('mental')">Mental Health</button>
            <button class="art-filter" data-cat="fitness" onclick="filterArticles('fitness')">Fitness</button>
            <button class="art-filter" data-cat="sleep" onclick="filterArticles('sleep')">Sleep</button>
        </div>

        <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(300px,1fr)); gap:24px; width:100%;" id="articleGrid">

            <?php
            $curated = [
              ['cat'=>'cycle','tag'=>'Cycle Health','title'=>'Understanding Your Menstrual Cycle','desc'=>'Learn the 4 phases and how each affects your mood, energy, and body.','read'=>'8 min','url'=>'https://www.healthline.com/health/womens-health/stages-of-menstrual-cycle','full'=>'Your menstrual cycle is a powerful monthly rhythm divided into 4 phases.<br><br><strong>Menstrual phase (Days 1-5):</strong> Hormone levels drop, causing the uterine lining to shed. Rest and iron-rich foods are essential.<br><br><strong>Follicular phase (Days 6-13):</strong> Estrogen rises as follicles develop. Energy and creativity peak &mdash; great time to start new projects.<br><br><strong>Ovulation (Day 14):</strong> An egg is released. You may feel more social, confident and energized.<br><br><strong>Luteal phase (Days 15-28):</strong> Progesterone rises. You may experience PMS symptoms like bloating or mood swings. Prioritize gentle movement and magnesium-rich foods.<br><br>Understanding this cycle helps you work <em>with</em> your body, not against it.'],
              ['cat'=>'nutrition','tag'=>'Nutrition','title'=>'Best Foods to Eat During Your Period','desc'=>'From dark chocolate to leafy greens, discover the foods that reduce cramps, bloating and fatigue during menstruation.','read'=>'6 min','url'=>'https://www.healthline.com/nutrition/foods-to-eat-on-your-period','full'=>'What you eat during your period can dramatically affect how you feel.<br><br><strong>Dark berries &amp; leafy greens</strong> &mdash; rich in iron and antioxidants to replenish blood loss.<br><strong>Dark chocolate (70%+)</strong> &mdash; contains magnesium which eases cramps and boosts serotonin.<br><strong>Fatty fish (salmon, sardines)</strong> &mdash; omega-3s reduce inflammation and period pain.<br><strong>Turmeric &amp; ginger</strong> &mdash; natural anti-inflammatory agents that relieve cramps.<br><strong>Avocado</strong> &mdash; healthy fats and potassium to reduce bloating.<br><br><strong>Avoid:</strong> Excess salt (causes bloating), caffeine (worsens cramps), processed sugars (spikes and crashes energy).<br><br>Eating mindfully during your cycle is one of the most powerful tools you have for wellness.'],
              ['cat'=>'mental','tag'=>'Mental Health','title'=>'How Hormones Affect Your Mood','desc'=>'Estrogen, progesterone, serotonin &mdash; understand the connection between your cycle and your emotional wellbeing.','read'=>'7 min','url'=>'https://www.verywellmind.com/how-hormones-affect-mental-health-5215466','full'=>'Hormones are your brain\'s chemical messengers, and they fluctuate significantly throughout your cycle.<br><br><strong>Estrogen</strong> boosts serotonin and dopamine &mdash; the feel-good chemicals. When estrogen is high (follicular/ovulation phase), many people feel happy, social and motivated.<br><br><strong>Progesterone</strong> has a calming effect but can also cause fatigue and low mood when it drops sharply before your period.<br><br><strong>PMS &amp; PMDD:</strong> In the luteal phase, the drop in estrogen can trigger anxiety, irritability, and sadness. This is completely normal. Tracking your mood helps distinguish hormonal patterns from clinical concerns.<br><br><strong>Tips:</strong><br>- Journal your emotions daily<br>- Exercise releases endorphins that counteract mood dips<br>- Limit alcohol &mdash; it worsens anxiety<br>- Talk to a doctor if mood swings are severely disruptive'],
              ['cat'=>'fitness','tag'=>'Fitness','title'=>'Cycle Syncing Your Workouts','desc'=>'Discover how to align your exercise routine with your menstrual phases for maximum results and minimal burnout.','read'=>'9 min','url'=>'https://www.mindbodygreen.com/articles/cycle-syncing-workouts','full'=>'Cycle syncing means adapting your lifestyle &mdash; including workouts &mdash; to match your hormonal phases.<br><br><strong>Menstrual (Days 1-5):</strong> Rest or gentle yoga. Your body is working hard. A restorative walk is perfect.<br><br><strong>Follicular (Days 6-13):</strong> Energy is rising! Great time for cardio, HIIT, dance, or trying a new fitness class.<br><br><strong>Ovulation (Day 14):</strong> Peak strength and endurance. Push yourself &mdash; heavy lifting, intense runs, group sports.<br><br><strong>Luteal (Days 15-28):</strong> Shift to moderate intensity. Pilates, swimming, barre, and strength training at comfortable weights.<br><br>Listening to your body this way prevents burnout, reduces injury risk, and actually improves fitness results.'],
              ['cat'=>'sleep','tag'=>'Sleep','title'=>'Why Your Sleep Changes With Your Cycle','desc'=>'Progesterone, body temperature, and insomnia &mdash; the surprising ways your hormones disrupt and improve your sleep.','read'=>'5 min','url'=>'https://www.sleepfoundation.org/women-sleep/menstrual-cycle-and-sleep','full'=>'Sleep quality and duration shift throughout your menstrual cycle, often without you realizing why.<br><br><strong>Follicular phase:</strong> Sleep is generally great. Estrogen promotes deeper, more restorative REM sleep.<br><br><strong>Ovulation:</strong> A small LH surge just before ovulation can slightly raise body temperature, causing lighter sleep.<br><br><strong>Luteal phase:</strong> Progesterone rises &mdash; it is a natural sedative, so you may feel sleepier. But the drop before your period causes insomnia and night sweats for many people.<br><br><strong>Menstrual phase:</strong> Cramps and discomfort can disrupt sleep. A heating pad and magnesium supplement help significantly.<br><br><strong>Tips:</strong> Avoid screens 1hr before bed, keep a consistent sleep schedule, and try chamomile tea during your luteal phase.'],
              ['cat'=>'mental','tag'=>'Self-Care','title'=>'Building a Period Self-Care Routine','desc'=>'Simple, science-backed self-care rituals that ease PMS, reduce stress, and help you feel your best every day.','read'=>'6 min','url'=>'https://www.self.com/story/period-self-care','full'=>'A thoughtful self-care routine tuned to your cycle can transform how you experience each month.<br><br><strong>Morning:</strong> Start with warm lemon water, a 5-minute stretch, and journaling one thing you\'re grateful for.<br><br><strong>Movement:</strong> Even 15 minutes of movement &mdash; adjusted for your phase &mdash; releases endorphins and reduces cramps by up to 30%.<br><br><strong>Nutrition:</strong> Batch-cook iron-rich meals during your menstrual phase so nourishing food is always ready.<br><br><strong>Evening:</strong> Epsom salt baths relax muscles. Lavender essential oil reduces anxiety.<br><br><strong>Tracking:</strong> Use a mood/cycle app (like this one!) to identify your patterns. Awareness is the most powerful self-care tool there is.'],
            ];
            foreach ($curated as $i => $a):
            ?>
            <div class="art-card" data-cat="<?= $a['cat'] ?>" data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 80 ?>"
                 style="background:var(--bg-card); backdrop-filter:blur(16px); border:1px solid var(--border-light); border-radius:24px; padding:28px; cursor:pointer; transition:all 0.3s ease; text-align:left; box-sizing:border-box;"
                 onclick="openArticle(<?= $i ?>)">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
                    <span style="background:var(--color-primary-light); color:var(--color-primary); font-size:11px; font-weight:800; padding:4px 12px; border-radius:20px; text-transform:uppercase; letter-spacing:0.5px;"><?= $a['tag'] ?></span>
                    <span style="margin-left:auto; font-size:12px; color:var(--text-muted);">Time: <?= $a['read'] ?></span>
                </div>
                <h4 style="font-size:17px; font-weight:800; margin-bottom:10px; color:var(--text-primary); text-align:left;"><?= $a['title'] ?></h4>
                <p style="font-size:13px; color:var(--text-secondary); line-height:1.6; text-align:left;"><?= $a['desc'] ?></p>
                <div style="margin-top:16px; display:flex; gap:10px; align-items:center;">
                    <button class="btn btn-primary" style="padding:8px 18px; font-size:13px; border-radius:20px;">Read Article</button>
                    <a href="<?= $a['url'] ?>" target="_blank" rel="noopener" onclick="event.stopPropagation()" style="font-size:12px; color:var(--text-muted); text-decoration:none;">Source</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>


    <!-- Article Modal Reader -->
    <div id="articleModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5); backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:20px;">
        <div style="background:var(--bg-card); border-radius:32px; max-width:760px; width:100%; max-height:85vh; overflow-y:auto; box-shadow:0 40px 80px rgba(0,0,0,0.2); position:relative; padding:48px 40px;">
            <button onclick="closeArticle()" style="position:absolute; top:20px; right:20px; background:var(--color-primary-light); border:none; border-radius:50%; width:40px; height:40px; font-size:18px; cursor:pointer; color:var(--color-primary); display:flex; align-items:center; justify-content:center;">&times;</button>
            <div id="modalContent"></div>
        </div>
    </div>

    <style>
    .content-wrapper {
        background: var(--bg-body);
        min-height: 100vh;
    }
    .art-filter {
        padding: 8px 20px; border-radius: 50px; border: 2px solid var(--color-primary-light);
        background: white; color: var(--color-primary); font-weight: 700; font-size: 13px;
        cursor: pointer; transition: all 0.2s;
    }
    .art-filter.active, .art-filter:hover {
        background: var(--color-primary); color: white; border-color: var(--color-primary);
    }
    .art-card:hover {
        transform: translateY(-6px) !important;
        box-shadow: var(--shadow-lg) !important;
        border-color: var(--color-primary) !important;
    }
    #articleModal.open { display: flex !important; }
    </style>

    <script>
    const articles = <?= json_encode(array_values($curated)) ?>;

    function filterArticles(cat) {
        document.querySelectorAll('.art-filter').forEach(b => b.classList.remove('active'));
        document.querySelector('[data-cat="'+cat+'"]').classList.add('active');
        document.querySelectorAll('.art-card').forEach(card => {
            card.style.display = (cat === 'all' || card.dataset.cat === cat) ? 'block' : 'none';
        });
    }

    function openArticle(idx) {
        const a = articles[idx];
        document.getElementById('modalContent').innerHTML = `
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
                <div>
                    <span style="background:var(--color-primary-light); color:var(--color-primary); font-size:11px; font-weight:800; padding:4px 12px; border-radius:20px;">${a.tag}</span>
                    <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Time: ${a.read}</div>
                </div>
            </div>
            <h2 style="font-size:26px; font-weight:900; color:var(--text-primary); margin-bottom:20px; line-height:1.3;">${a.title}</h2>
            <div style="font-size:15px; color:var(--text-secondary); line-height:1.8;">${a.full}</div>
            <div style="margin-top:32px; padding-top:20px; border-top:1px solid rgba(0,0,0,0.07); display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                <a href="${a.url}" target="_blank" rel="noopener" class="btn btn-primary" style="border-radius:20px; font-size:14px; padding:10px 24px;">Read Full Article</a>
                <button onclick="closeArticle()" style="background:transparent; border:2px solid var(--border-color); border-radius:20px; padding:10px 24px; font-size:14px; cursor:pointer; color:var(--text-secondary); font-weight:700;">Close</button>
            </div>`;
        document.getElementById('articleModal').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeArticle() {
        document.getElementById('articleModal').classList.remove('open');
        document.body.style.overflow = '';
    }
    document.getElementById('articleModal').addEventListener('click', function(e) {
        if (e.target === this) closeArticle();
    });
    </script>
    
    <!-- Self-Care Reminders -->
    <div class="card" style="background:var(--bg-card); backdrop-filter:blur(10px); border:1px solid var(--border-light); padding:40px; text-align:center;" data-aos="zoom-in-up">
        <h3 style="margin-bottom:24px; font-size:24px;"><i class="fa-solid fa-bell" style="color:var(--color-sage);"></i> Gentle Reminders</h3>

        <div class="grid grid-4">
            <div style="transition:transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='none'">
                <div style="font-size:40px; margin-bottom:12px; color:var(--color-primary);"><i class="fa-solid fa-droplet"></i></div>
                <p style="font-size:14px; font-weight:700;">Stay Hydrated</p>
                <p style="font-size:12px; color:var(--text-secondary);">Sip water throughout the day</p>
            </div>
            <div style="transition:transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='none'">
                <div style="font-size:40px; margin-bottom:12px; color:var(--color-sage);"><i class="fa-solid fa-couch"></i></div>
                <p style="font-size:14px; font-weight:700;">Move Gently</p>
                <p style="font-size:12px; color:var(--text-secondary);">Listen to your body's needs</p>
            </div>
            <div style="transition:transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='none'">
                <div style="font-size:40px; margin-bottom:12px; color:var(--color-secondary-dark);"><i class="fa-solid fa-bed"></i></div>
                <p style="font-size:14px; font-weight:700;">Rest Well</p>
                <p style="font-size:12px; color:var(--text-secondary);">Prioritize your beauty sleep</p>
            </div>
            <div style="transition:transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='none'">
                <div style="font-size:40px; margin-bottom:12px; color:var(--color-warning);"><i class="fa-solid fa-apple-whole"></i></div>
                <p style="font-size:14px; font-weight:700;">Nourish</p>
                <p style="font-size:12px; color:var(--text-secondary);">Fuel yourself with love</p>
            </div>
        </div>
    </div>
</div>
</div>

<?php require_once 'includes/footer.php'; ?>
