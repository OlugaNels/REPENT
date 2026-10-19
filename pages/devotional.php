<?php require_once '../includes/header.php'; ?>

<!-- DEVOTIONAL HERO -->
<div class="devotional-hero-verse">
    <div class="container">
        <span class="section-label" style="display:block;text-align:center;margin-bottom:16px;">Daily Word</span>
        <p class="big-verse">"Your word is a lamp to my feet and a light to my path."</p>
        <p class="big-verse-ref">— Psalm 119:105</p>
    </div>
</div>

<!-- VERSE OF THE DAY -->
<section class="section" style="background: var(--off-white);">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Verse of the Day</span>
            <h2 class="section-title">Today's Scripture</h2>
        </div>

        <!-- Featured Devotional -->
        <div style="background:var(--black);border-radius:8px;padding:48px;margin-bottom:48px;display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center;">
            <div>
                <div style="font-size:11px;letter-spacing:0.2em;text-transform:uppercase;color:var(--gold);margin-bottom:16px;">Featured — <?= date('l, d F Y') ?></div>
                <p style="font-family:var(--font-display);font-style:italic;font-size:clamp(22px,3vw,36px);color:var(--white);font-weight:300;line-height:1.4;margin-bottom:12px;">
                    "Do not conform to the pattern of this world, but be transformed by the renewing of your mind."
                </p>
                <p style="font-size:14px;color:var(--gold);margin-bottom:24px;">— Romans 12:2</p>
                <p style="font-size:15px;color:rgba(255,255,255,0.65);line-height:1.75;">
                    The Apostle Paul calls us to a radical counter-cultural life. The world offers patterns of thinking rooted in pride, instant gratification, and self-sufficiency. God calls us to something entirely different — a continual transformation that begins in the mind. Renewing the mind means immersing ourselves in God's Word, choosing to think and respond according to His truth rather than the world's standards.
                </p>
                <div style="margin-top:28px;display:flex;gap:12px;flex-wrap:wrap;">
                    <span style="padding:6px 16px;background:rgba(201,168,76,0.15);color:var(--gold);border-radius:20px;font-size:12px;letter-spacing:0.1em;">Transformation</span>
                    <span style="padding:6px 16px;background:rgba(201,168,76,0.15);color:var(--gold);border-radius:20px;font-size:12px;letter-spacing:0.1em;">Renewal</span>
                    <span style="padding:6px 16px;background:rgba(201,168,76,0.15);color:var(--gold);border-radius:20px;font-size:12px;letter-spacing:0.1em;">Romans</span>
                </div>
            </div>
            <div style="text-align:center;">
                <div style="font-family:var(--font-display);font-size:180px;line-height:1;color:rgba(201,168,76,0.12);font-weight:700;">†</div>
            </div>
        </div>

        <!-- Devotional Cards Grid -->
        <div class="devotionals-grid">

            <div class="devotional-card">
                <div class="devotional-card-top">
                    <div class="devotional-verse-tag">Repentance</div>
                    <div class="devotional-verse-text">"Repent, for the kingdom of heaven has come near."</div>
                    <div class="devotional-verse-ref">— Matthew 4:17</div>
                </div>
                <div class="devotional-card-body">
                    <h3 class="devotional-card-title">The Call to Repentance</h3>
                    <p class="devotional-card-excerpt">Repentance is not simply feeling sorry for sin — it is a decisive turn. A 180-degree change in direction. Jesus begins His ministry not with a self-help programme but with a call to repent. This is the foundation of the Christian life: acknowledging that our way is broken and choosing God's way instead.</p>
                    <a href="#" class="devotional-card-link">Read Full Devotional →</a>
                </div>
            </div>

            <div class="devotional-card">
                <div class="devotional-card-top">
                    <div class="devotional-verse-tag">Peace</div>
                    <div class="devotional-verse-text">"Be still, and know that I am God."</div>
                    <div class="devotional-verse-ref">— Psalm 46:10</div>
                </div>
                <div class="devotional-card-body">
                    <h3 class="devotional-card-title">Finding Peace in the Storm</h3>
                    <p class="devotional-card-excerpt">In a world that rewards busyness and productivity, God invites us into stillness. Not passivity — but a confident rest in the knowledge that He holds everything in His hands. When life feels overwhelming, we are not called to figure it all out. We are called to be still and trust.</p>
                    <a href="#" class="devotional-card-link">Read Full Devotional →</a>
                </div>
            </div>

            <div class="devotional-card">
                <div class="devotional-card-top">
                    <div class="devotional-verse-tag">Strength</div>
                    <div class="devotional-verse-text">"I can do all things through Christ who strengthens me."</div>
                    <div class="devotional-verse-ref">— Philippians 4:13</div>
                </div>
                <div class="devotional-card-body">
                    <h3 class="devotional-card-title">Strength in Surrender</h3>
                    <p class="devotional-card-excerpt">True strength is not found in self-reliance. Paul wrote this verse from a prison cell — not a stage. He had learned contentment in every circumstance because his strength came from Christ, not his conditions. Whatever you are facing today, Christ's strength is available to you.</p>
                    <a href="#" class="devotional-card-link">Read Full Devotional →</a>
                </div>
            </div>

            <div class="devotional-card">
                <div class="devotional-card-top">
                    <div class="devotional-verse-tag">Identity</div>
                    <div class="devotional-verse-text">"Therefore, if anyone is in Christ, he is a new creation."</div>
                    <div class="devotional-verse-ref">— 2 Corinthians 5:17</div>
                </div>
                <div class="devotional-card-body">
                    <h3 class="devotional-card-title">You Are Made New</h3>
                    <p class="devotional-card-excerpt">The gospel is not a self-improvement plan — it is a new birth. When we come to Christ, the old is gone. We don't carry our old identity into the new life. God declares us new creations. Not improved versions of ourselves, but entirely new people, reborn in His image.</p>
                    <a href="#" class="devotional-card-link">Read Full Devotional →</a>
                </div>
            </div>

            <div class="devotional-card">
                <div class="devotional-card-top">
                    <div class="devotional-verse-tag">Mission</div>
                    <div class="devotional-verse-text">"Go and make disciples of all nations."</div>
                    <div class="devotional-verse-ref">— Matthew 28:19</div>
                </div>
                <div class="devotional-card-body">
                    <h3 class="devotional-card-title">Living on Mission</h3>
                    <p class="devotional-card-excerpt">The Great Commission is not just for missionaries and pastors. It is the calling of every believer. You are a missionary in your school, workplace, family, and community. God sends us not just to places on a map, but to the people in our everyday lives who need to hear about His love.</p>
                    <a href="#" class="devotional-card-link">Read Full Devotional →</a>
                </div>
            </div>

            <div class="devotional-card">
                <div class="devotional-card-top">
                    <div class="devotional-verse-tag">Faith</div>
                    <div class="devotional-verse-text">"Now faith is the substance of things hoped for, the evidence of things not seen."</div>
                    <div class="devotional-verse-ref">— Hebrews 11:1</div>
                </div>
                <div class="devotional-card-body">
                    <h3 class="devotional-card-title">Walking by Faith</h3>
                    <p class="devotional-card-excerpt">Faith is not wishful thinking. It is a confident trust in a God who has proven Himself faithful throughout history and throughout your own life. Every step of obedience, even when the outcome is unclear, is an act of faith that pleases God and honours His name.</p>
                    <a href="#" class="devotional-card-link">Read Full Devotional →</a>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- SUBSCRIBE CTA -->
<section class="faith-banner">
    <div class="faith-banner-label">Stay in the Word</div>
    <h2 class="faith-banner-title">"Let the word of Christ dwell in you richly."</h2>
    <p class="faith-banner-ref">— Colossians 3:16</p>
    <a href="newsletter.php" class="btn btn-gold">Subscribe for Weekly Devotionals</a>
</section>

<?php require_once '../includes/footer.php'; ?>
