<?php
// Extracted from Eswatini Vegetable Production Guidelines
return [
    'Tomatoes' => [
        'duration_days' => 120, // 90-120 days
        'activities' => [
            ['day' => 14, 'type' => 'staking', 'title' => 'Staking', 'description' => 'Staking should be done not later than 2 weeks from planting to avoid root damage.', 'color' => '#6c757d'],
            ['day' => 28, 'type' => 'fertilizer', 'title' => 'Top Dressing 1', 'description' => 'Apply 100kg LAN and 50kg KCl per hectare (4 weeks after planting).', 'color' => '#0d6efd'],
            ['day' => 49, 'type' => 'fertilizer', 'title' => 'Top Dressing 2', 'description' => 'Apply LAN and KCl again (2-3 weeks after first top dressing).', 'color' => '#0d6efd'],
            ['day' => 70, 'type' => 'harvest', 'title' => 'First Harvest', 'description' => 'Fruits mature (green) at 70-80 days. Harvest in early morning.', 'color' => '#dc3545'],
        ],
        'diseases' => [
            'Early Blight' => ['symptoms' => 'Circular brown spots on lower leaves', 'treatment' => 'Spray Bravo (20ml) or Amistar (20ml)'],
            'Late Blight' => ['symptoms' => 'Blue grey water soaked spots on leaves turning brown', 'treatment' => 'Spray Ridomil (50g) or Bravo (20ml)'],
            'American Bollworm' => ['symptoms' => 'Larvae feed on fruit', 'treatment' => 'Decis Forte (5ml) or Cypermetrin (20ml)']
        ]
    ],
    'Cabbage' => [
        'duration_days' => 90, // 75-120 days
        'activities' => [
            ['day' => 21, 'type' => 'weeding', 'title' => 'First Weeding', 'description' => 'Weeding should be done 2-3 weeks after transplanting.', 'color' => '#ffc107'],
            ['day' => 28, 'type' => 'fertilizer', 'title' => 'Top Dressing 1', 'description' => 'Apply LAN 5g/plant (4 weeks after planting).', 'color' => '#0d6efd'],
            ['day' => 42, 'type' => 'fertilizer', 'title' => 'Top Dressing 2', 'description' => 'Apply LAN 5g/plant (6 weeks after planting).', 'color' => '#0d6efd'],
            ['day' => 90, 'type' => 'harvest', 'title' => 'Harvest', 'description' => 'Harvest when head is compact and firm to touch. Do not leave stumps in ground.', 'color' => '#dc3545'],
        ],
        'diseases' => [
            'Diamond Back Moth' => ['symptoms' => 'Larva feeds on lower leaf, creating window-paning effect', 'treatment' => 'Malasol (12ml)'],
            'Aphids' => ['symptoms' => 'Colonies on young stem, distortion of shoots', 'treatment' => 'Profenofos (20ml) or Aphicide Plus (100ml)'],
            'Black Rot' => ['symptoms' => 'White to light brown lesions with purple/black margin on leaves', 'treatment' => 'Use resistant varieties, crop rotation']
        ]
    ],
    'Spinach' => [
        'duration_days' => 40, 
        'activities' => [
            ['day' => 14, 'type' => 'thinning', 'title' => 'Thinning', 'description' => 'Thin plants 2-3 weeks after emergence.', 'color' => '#6c757d'],
            ['day' => 21, 'type' => 'fertilizer', 'title' => 'Top Dressing', 'description' => 'Side dress with 200kg/ha LAN (3 weeks after planting).', 'color' => '#0d6efd'],
            ['day' => 35, 'type' => 'harvest', 'title' => 'First Harvest', 'description' => 'Start harvesting at 4-5 weeks. Avoid damaging growing points.', 'color' => '#dc3545'],
        ],
        'diseases' => [
            'Leaf Miner' => ['symptoms' => 'Larvae mines leaves producing discoloured patches', 'treatment' => 'Spray Agromectin (15ml)'],
            'Cercospora Leaf Spot' => ['symptoms' => 'Light grey spots with darker outline', 'treatment' => 'Bravo 40ml or Ridomil 20g'],
        ]
    ],
    'Carrots' => [
        'duration_days' => 85, // 70-100 days
        'activities' => [
            ['day' => 25, 'type' => 'thinning', 'title' => 'Thinning', 'description' => 'Thin plants 3-4 weeks after germination. Final spacing 3cm.', 'color' => '#6c757d'],
            ['day' => 40, 'type' => 'fertilizer', 'title' => 'Top Dressing', 'description' => 'Apply 80 kg/ha LAN (4-8 weeks after germination).', 'color' => '#0d6efd'],
            ['day' => 85, 'type' => 'harvest', 'title' => 'Harvest', 'description' => 'Harvest when diameter is at least 20mm.', 'color' => '#dc3545'],
        ]
    ],
    'Potatoes' => [
        'duration_days' => 110, // 14-16 weeks
        'activities' => [
            ['day' => 21, 'type' => 'weeding', 'title' => 'Weeding & Earthing Up', 'description' => 'Earth up when plants reach 10-15cm height to cover tubers and prevent greening.', 'color' => '#ffc107'],
            ['day' => 35, 'type' => 'fertilizer', 'title' => 'Top Dressing', 'description' => 'Broadcast or band top dressing fertilizer 4-6 weeks after emergence.', 'color' => '#0d6efd'],
            ['day' => 90, 'type' => 'vine_killing', 'title' => 'Vine Killing', 'description' => 'Kill vines 2-3 weeks before harvesting to allow skin to set.', 'color' => '#6c757d'],
            ['day' => 110, 'type' => 'harvest', 'title' => 'Harvest', 'description' => 'Harvest when 95% of leaves have dried and skin does not peel when rubbed.', 'color' => '#dc3545'],
        ]
    ]
];
