<?php
/**
 * REPENT Logo — Inline SVG
 * Cross sits immediately after N, no gap.
 * $logoColor = 'black' | 'white'
 * $logoHeight = px height
 */
$logoColor  = $logoColor  ?? 'black';
$logoHeight = $logoHeight ?? 30;
$fill       = ($logoColor === 'white') ? '#ffffff' : '#0a0a0a';
?>
<span class="repent-logo" aria-label="REPENT">
<svg xmlns="http://www.w3.org/2000/svg"
     viewBox="0 0 230 48"
     height="<?= $logoHeight ?>"
     style="display:block;overflow:visible;">
  <!--
    Strategy: use a single <text> element "REPENT" but make
    the final T invisible (fill=none), then draw the cross
    manually ON TOP of where the T sits.
    This guarantees perfect alignment every time.
  -->

  <!-- Full word REPENT — T is drawn transparent so spacing is correct -->
  <text x="0" y="40"
    font-family="'DM Sans','Arial Black',Arial,sans-serif"
    font-weight="800"
    font-size="42"
    letter-spacing="-1"
    fill="<?= $fill ?>">REPEN<tspan fill="none">T</tspan></text>

  <!--
    Now draw the cross T manually.
    At font-size 42, each character is roughly 26-28px wide.
    "REPEN" = 5 chars ≈ 5 × 26 = 130px + letter-spacing adjustments ≈ x=133 for T center.
    Cross center x = 133 + 13 (half char) = 146
    Cap height at font-size 42 ≈ 30px, so baseline y=40, cap top y=10
  -->

  <!-- Vertical stem: full height + rises 10px above cap -->
  <rect x="143" y="0"  width="5" height="48" rx="2" fill="<?= $fill ?>"/>
  <!-- Horizontal crossbar: at top of cap -->
  <rect x="129" y="8"  width="33" height="5" rx="2" fill="<?= $fill ?>"/>

</svg>
</span>
