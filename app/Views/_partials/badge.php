<?php
/**
 * Partial: badge.php
 *
 * Status badge with color + text label.
 * Color is NEVER the sole indicator — a text label is always present.
 *
 * Tailwind classes are written literally (not string interpolated) so they
 * are not purged by the Tailwind CDN.
 *
 * Accepted variables:
 * @var string $label (required)
 * @var string $color (required) 'green' | 'red' | 'yellow' | 'blue' | 'gray'
 */

$colorMap = [
    'green'  => 'bg-green-100 text-green-800',
    'red'    => 'bg-red-100 text-red-800',
    'yellow' => 'bg-yellow-100 text-yellow-800',
    'blue'   => 'bg-blue-100 text-blue-800',
    'gray'   => 'bg-gray-100 text-gray-800',
];

$classes = $colorMap[$color ?? 'gray'] ?? $colorMap['gray'];
?>
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $classes ?>">
  <?= esc($label ?? '') ?>
</span>
