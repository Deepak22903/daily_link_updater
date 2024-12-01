<?php

namespace DailyLinkUpdater;
// Modify PostLinkUpdater class to use dynamic configurations
class PostLinkUpdater {
    private $post_configs = [];
    
    public function __construct() {
        $this->load_post_configs();
    }
    
    private function load_post_configs() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'link_updater_posts';
        $posts = $wpdb->get_results("SELECT * FROM $table_name");
        
        foreach ($posts as $post) {
            $this->post_configs[$post->post_id] = array(
                'url' => $post->source_url,
                'type' => $post->post_type,
                'link_patterns' => explode("\n", str_replace("\r", "", $post->link_patterns)),
                'link_text' => $post->link_text
            );
        }
    }

    private $date_formats = [
        'display' => 'F j, Y',          // November 1, 2024
        'dot' => 'j.n.Y',              // 1.11.2024
        'dot_alt' => 'd.n.y',              // 01.11.24
        'dot_alt_2' => 'd.n.Y',              // 01.11.2024
        'ordinal' => 'jS F, Y',        // 1st November, 2024
        'underscore' => 'jS_F_Y',      // 1st_November_2024
        'id' => 'F_j_Y'                // November_1_2024
    ];

    public function get_links_from_source($source_url, $config) {
        $html = @file_get_contents($source_url);
        if ($html === false) {
            custom_log("Failed to retrieve content from $source_url", 'error');
            return [];
        }

        $today_links = [];
        
        switch ($config['type']) {
            case 'match_masters':
                $today_links = $this->extract_match_masters_links($html);
                break;
            case 'hit_it_rich':
                $today_links = $this->extract_hit_it_rich_links($html);
                break;
            case 'zynga_poker':
                $today_links = $this->extract_zynga_poker_links($html);
                break;
            case 'board_kings':
                $today_links = $this->extract_board_kings_links($html);
                break;
            case 'coin_master':
                $today_links = $this->extract_coin_master_links($html);
                break;
            case 'bingo_bash':
                $today_links = $this->extract_bingo_bash_links($html);
                break;
            case 'house_of_fun':
                $today_links = $this->extract_house_of_fun_links($html);
                break;
            case 'pop_slots':
                $today_links = $this->extract_pop_slots_links($html);
                break;
            case 'solitaire_grand_harvest':
                $today_links = $this->extract_solitaire_grand_harvest_links($html);
                break;
            case 'coin_tales':
                $today_links = $this->extract_coin_tales_links($html);
                break;
            case 'jackpot_party':
                $today_links = $this->extract_jackpot_party_links($html);
                break;
            case 'crazy_fox':
                $today_links = $this->extract_crazy_fox_links($html);
                break;
            case 'heart_of_vegas':
                $today_links = $this->extract_heart_of_vegas_links($html);
                break;
            case 'cash_frenzy':
                $today_links = $this->extract_cash_frenzy_links($html);
                break;
            case 'wsop_chips':
                $today_links = $this->extract_wsop_chips_links($html);
                break;
            case 'caesars_casino':
                $today_links = $this->extract_caesars_casino_links($html);
                break;
            case 'doubleu_casino':
                $today_links = $this->extract_doubleu_casino_links($html);
                break;
            case 'doubledown_casino':
                $today_links = $this->extract_doubledown_casino_links($html);
                break;
            case 'huuuge_casino':
                $today_links = $this->extract_huuuge_casino_links($html);
                break;
            case 'quick_hit_slots':
                $today_links = $this->extract_quick_hit_slots_links($html);
                break;
            case 'bingo_blitz':
                $today_links = $this->extract_bingo_blitz_links($html);
                break;
            case 'slotomania':
                $today_links = $this->extract_slotomania_links($html);
                break;
            case 'gold_fish':
                $today_links = $this->extract_gold_fish_links($html);
                break;
            case 'club_vegas':
                $today_links = $this->extract_club_vegas_links($html);
                break;
            case 'bingo_aloha':
                $today_links = $this->extract_bingo_aloha_links($html);
                break;
            case 'animal_kingdom':
                $today_links = $this->extract_animal_kingdom_links($html);
                break;
            case 'backgammon_lord':
                $today_links = $this->extract_backgammon_lord_links($html);
                break;
            case 'my_vegas_slots':
                $today_links = $this->extract_my_vegas_slots_links($html);
                break;
            case 'piggy_go':
                $today_links = $this->extract_piggy_go_links($html);
                break;
            case 'pirate_kings':
                $today_links = $this->extract_pirate_kings_links($html);
                break;
            case 'big_fish':
                $today_links = $this->extract_big_fish_links($html);
                break;
            case 'billionaire_casino':
                $today_links = $this->extract_billionaire_casino_links($html);
                break;
            case 'jackpot_world':
                $today_links = $this->extract_jackpot_world_links($html);
                break;
            case 'game_of_thrones':
                $today_links = $this->extract_game_of_thrones_links($html);
                break;
            case 'wizard_of_oz':
                $today_links = $this->extract_wizard_of_oz_links($html);
                break;
            case 'gaminator':
                $today_links = $this->extract_gaminator_links($html);
                break;
            case 'travel_town':
                $today_links = $this->extract_travel_town_links($html);
                break;
            case 'family_island':
                $today_links = $this->extract_family_island_links($html);
                break;
            case 'spin_a_spell':
                $today_links = $this->extract_spin_a_spell_links($html);
                break;
            case '8_ball_pool':
                $today_links = $this->extract_8_ball_pool_links($html);
                break;
            case 'willy_wonka':
                $today_links = $this->extract_willy_wonka_links($html);
                break;
            case 'scatter_slots':
                $today_links = $this->extract_scatter_slots_links($html);
                break;
            case 'lotsa_slots':
                $today_links = $this->extract_lotsa_slots_links($html);
                break;
            case 'mgm_live_slots':
                $today_links = $this->extract_mgm_live_slots_links($html);
                break;
            case 'slotpark':
                $today_links = $this->extract_slotpark_links($html);
                break;
            case 'bingo_holiday':
                $today_links = $this->extract_bingo_holiday_links($html);
                break;
            case 'dice_dreams':
                $today_links = $this->extract_dice_dreams_links($html);
                break;
            case 'island_king':
                $today_links = $this->extract_island_king_links($html);
                break;
            case 'gametwist_slots':
                $today_links = $this->extract_gametwist_slots_links($html);
                break;
        }
        

        return $today_links;
    }
    
    private function extract_gametwist_slots_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot_alt_2']); // Using dot format: "01.11.2024"
        
        foreach ($this->post_configs[2075]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Gametwist Slots from techyhigher.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_island_king_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1984]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Island King from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    
    private function extract_dice_dreams_links($html) {
    $links = [];
    $date = date('d F Y'); // Format: "02 November 2024"
    
    foreach ($this->post_configs[1922]['link_patterns'] as $pattern) {
        // Match section between heading with today's date and next heading
        $section_pattern = '/<h4[^>]*>.*?Updated On: ' . preg_quote($date, '/') . 
            '.*?<\/h4>(.*?)(?=<h4|$)/si';
        
        if (preg_match($section_pattern, $html, $section)) {
            // Extract href links from the matched section
            $link_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>/i';
            preg_match_all($link_pattern, $section[1], $matches);
            
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
    }
    
    $links = array_unique($links); // Remove duplicates
    custom_log("Fetched " . count($links) . " links for Dice Dreams from coinscrazy.com: " . implode(', ', $links));
    return $links;
}
    
    private function extract_bingo_holiday_links($html) {
    $date_pattern = '<span id="Bingo_Holiday_Free_Credits_Link_-_' . date($this->date_formats['underscore']) . '">';
    $pos = strpos($html, $date_pattern);
    
    if ($pos === false) {
        custom_log("Could not find section with today's date pattern", 'warning');
        return [];
    }

    $section_start = $pos;
    $next_h4_pos = strpos($html, '<h4', $pos + strlen($date_pattern));
    $section_end = $next_h4_pos !== false ? $next_h4_pos : strlen($html);
    $section_content = substr($html, $section_start, $section_end - $section_start);

    $links = [];
    foreach ($this->post_configs[2052]['link_patterns'] as $pattern) {
        preg_match_all('/<a href="(' . preg_quote($pattern, '/') . '[^"]+)"[^>]*>/i', 
            $section_content, $matches);
        
        if (!empty($matches[1])) {
            $links = array_merge($links, $matches[1]);
        }
    }
    
    $links = array_unique($links);
    $links = array_reverse($links);
    
    custom_log("Fetched " . count($links) . " links for Bingo Holiday from rezortricks.com: " . implode(', ', $links));
    return $links;
}
    
    private function extract_slotpark_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[2034]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Slotpark from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_mgm_live_slots_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[2036]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for MGM Live Slots from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_lotsa_slots_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[2038]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Lotsa Slots from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_scatter_slots_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[2042]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Scatter Slots from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_willy_wonka_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[2048]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Willy Wonka from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_8_ball_pool_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[2050]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for 8 Ball Pool from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_spin_a_spell_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot_alt']); // Using dot format: "01.11.24"
        
        foreach ($this->post_configs[2045]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Spin a Spell from rezortricks.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_family_island_links($html) {
    $links = [];
    $date = date('F j, Y'); // Format: "November 3, 2024"
    
    // Create pattern to match content between today's date heading and next heading
    $pattern = '/<strong>' . preg_quote($date, '/') . ':<\/strong><\/p>\s*<div class="reward-box">(.*?)(?=<p>|$)/s';
    
    if (preg_match($pattern, $html, $match)) {
        // Extract all data-link attributes from the matched section
        foreach ($this->post_configs[1972]['link_patterns'] as $pattern) {
            $link_pattern = '/data-link="(' . preg_quote($pattern, '/') . '[^"]+)"/';
            preg_match_all($link_pattern, $match[1], $link_matches);
            
            if (!empty($link_matches[1])) {
                $links = array_merge($links, $link_matches[1]);
            }
        }
    }
    
    $links = array_unique($links); // Remove duplicates
    custom_log("Fetched " . count($links) . " links for Family Island from simplegamingguide.com: " . implode(', ', $links));
    return $links;
}
    
    private function extract_travel_town_links($html) {
    $links = [];
    $date = date('F j, Y'); // Format: "November 3, 2024"
    
    // Create pattern to match content between today's date heading and next heading
    $pattern = '/<strong>' . preg_quote($date, '/') . ':<\/strong><\/p>\s*<div class="reward-box">(.*?)(?=<p>|$)/s';
    
    if (preg_match($pattern, $html, $match)) {
        // Extract all data-link attributes from the matched section
        foreach ($this->post_configs[1990]['link_patterns'] as $pattern) {
            $link_pattern = '/data-link="(' . preg_quote($pattern, '/') . '[^"]+)"/';
            preg_match_all($link_pattern, $match[1], $link_matches);
            
            if (!empty($link_matches[1])) {
                $links = array_merge($links, $link_matches[1]);
            }
        }
    }
    
    $links = array_unique($links); // Remove duplicates
    custom_log("Fetched " . count($links) . " links for Travel Town from simplegamingguide.com: " . implode(', ', $links));
    return $links;
}
    

    
    private function extract_gaminator_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1963]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Gaminator from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_wizard_of_oz_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1970]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Wizard of Oz from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_game_of_thrones_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1976]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Game of Thrones from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    
    private function extract_jackpot_world_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1980]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Jackpot World from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    
    private function extract_billionaire_casino_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1980]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Billionaire Casino from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_big_fish_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1982]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Big Fish from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_pirate_kings_links($html) {
    $date_pattern = '<span id="Pirate_Kings_Free_Spins_Link_-_' . date($this->date_formats['underscore']) . '">';
    $pos = strpos($html, $date_pattern);
    
    if ($pos === false) {
        custom_log("Could not find section with today's date pattern", 'warning');
        return [];
    }

    $section_start = $pos;
    $next_h4_pos = strpos($html, '<h4', $pos + strlen($date_pattern));
    $section_end = $next_h4_pos !== false ? $next_h4_pos : strlen($html);
    $section_content = substr($html, $section_start, $section_end - $section_start);

    $links = [];
    foreach ($this->post_configs[1986]['link_patterns'] as $pattern) {
        preg_match_all('/<a href="(' . preg_quote($pattern, '/') . '[^"]+)"[^>]*>/i', 
            $section_content, $matches);
        
        if (!empty($matches[1])) {
            $links = array_merge($links, $matches[1]);
        }
    }
    
    $links = array_unique($links);
    $links = array_reverse($links);
    
    custom_log("Fetched " . count($links) . " links for Pirate Kings from rezortricks.com: " . implode(', ', $links));
    return $links;
}
    
    private function extract_piggy_go_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1988]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Piggy Go from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_my_vegas_slots_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1992]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for My Vegas Slots from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_backgammon_lord_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1994]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Backgammon Lord from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_animal_kingdom_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot_alt_2']); // Using dot format: "01.11.2024"
        
        foreach ($this->post_configs[2010]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Animal Kingdom from techyhigher.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_bingo_aloha_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1996]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Bingo Aloha from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_club_vegas_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1998]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Club Vegas from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_gold_fish_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[2000]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Gold Fish from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_slotomania_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1907]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Slotomania from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_bingo_blitz_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1910]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Bingo Blitz from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_quick_hit_slots_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1912]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Quick Hit Slots from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_huuuge_casino_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1914]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Huuuge Casino from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    
    private function extract_doubledown_casino_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1916]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for DoubleDown Casino from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    
    private function extract_doubleu_casino_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot_alt']); // Using dot format: "01.11.24"
        
        foreach ($this->post_configs[1919]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for DoubleU Casino from crazyashwin.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_caesars_casino_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1904]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Caesars Casino from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_wsop_chips_links($html) {
    $links = [];
    $date_pattern = date($this->date_formats['display']); // Using display format: "November 3, 2024"
    
    // Convert date pattern to the format used in the HTML (d F Y)
    $html_date = date('d F Y'); // "03 November 2024"
    
    // Find section with today's date heading
    $section_pattern = '/<p[^>]*><strong>' . preg_quote($html_date, '/') . 
        '<\/strong><\/p>\s*<ol[^>]*>(.*?)(?=<p[^>]*><strong>|$)/is';
            
    if (preg_match($section_pattern, $html, $section_match)) {
        foreach ($this->post_configs[1902]['link_patterns'] as $pattern) {
            // Match links from the section that match the pattern
            $link_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)"[^>]*>(?:\d+\+\s+)?Free\s+(?:Chips|Cards)<\/a>/i';
            
            preg_match_all($link_pattern, $section_match[1], $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
    }
    
    $links = array_unique($links); // Remove duplicates
    custom_log("Fetched " . count($links) . " links for WSOP Chips from freechipswsop.com: " . implode(', ', $links));
    return $links;
}
    
    private function extract_cash_frenzy_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1899]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Cash Frenzy from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_heart_of_vegas_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1893]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Heart of Vegas from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_crazy_fox_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1891]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Crazy Fox from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_jackpot_party_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1888]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Jackpot Party from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_coin_tales_links($html) {
    $links = [];
    $today = date('d F Y'); // Format: "03 November 2024"
    
    // Pattern to match heading with today's date and get the following link
    $regex_pattern = '/<h4[^>]*>.*?' . preg_quote($today, '/') . 
        '.*?<\/h4>.*?<a\s+href="([^"]+)".*?>/is';
    
    if (preg_match($regex_pattern, $html, $match)) {
        if (!empty($match[1])) {
            $links[] = $match[1];
        }
    }
    
    $links = array_unique($links); // Remove duplicates (though we expect only one)
    custom_log("Fetched " . count($links) . " links for Coin Tales: " . implode(', ', $links));
    return $links;
}
    
    private function extract_solitaire_grand_harvest_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1807]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Solitaire Grand Harvest from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_pop_slots_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[1785]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Pop Slots from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_house_of_fun_links($html) {
    $date_pattern = '<span id="HOF_todays_free_coins_and_spins_link-_' . date($this->date_formats['underscore']) . '">';
    $pos = strpos($html, $date_pattern);

    $section_start = $pos;
    $next_h4_pos = strpos($html, '<h4', $pos + strlen($date_pattern));
    $section_end = $next_h4_pos !== false ? $next_h4_pos : strlen($html);
    $section_content = substr($html, $section_start, $section_end - $section_start);

    $links = [];
    foreach ($this->post_configs[419]['link_patterns'] as $pattern) {
        preg_match_all('/<a href="(' . preg_quote($pattern, '/') . '[^"]+)"[^>]*>/i', 
            $section_content, $matches);
        
        if (!empty($matches[1])) {
            $links = array_merge($links, $matches[1]);
        }
    }
    
    $links = array_unique($links);
    $links = array_reverse($links);
    
    custom_log("Fetched " . count($links) . " links for House of Fun from rezortricks.com: " . implode(', ', $links));
    return $links;
}
    
    private function extract_bingo_bash_links($html) {
    // Create a new DOMDocument
    $dom = new DOMDocument();
    
    // Suppress warnings for malformed HTML
    @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    
    $date_string = 'Free Chips – ' . date($this->date_formats['human']);
    
    // Find all h4 elements with the specific date
    $xpath = new DOMXPath($dom);
    $h4_elements = $xpath->query("//h4[contains(., '{$date_string}')]");
    
    $links = [];
    
    // If no matching h4 found, log and return empty
    if ($h4_elements->length == 0) {
        custom_log("Could not find section with today's date pattern", 'warning');
        return [];
    }
    
    // Get the first matching h4 element
    $target_h4 = $h4_elements->item(0);
    
    // Find all links in the section after this h4
    $current = $target_h4;
    while ($current = $current->nextSibling) {
        // Stop if we hit the next h4 (end of section)
        if ($current->nodeName === 'h4') {
            break;
        }
        
        // Only process element nodes
        if ($current->nodeType === XML_ELEMENT_NODE) {
            $anchors = $current->getElementsByTagName('a');
            foreach ($anchors as $anchor) {
                $href = $anchor->getAttribute('href');
                $links[] = $href;
            }
        }
    }
    
    // Remove duplicates, reverse order
    $links = array_values(array_unique($links));
    $links = array_reverse($links);
    
    custom_log("Fetched " . count($links) . " links Bingo Bash from rezortricks.com: " . implode(', ', $links));
    return $links;
}
        
    private function extract_coin_master_links($html) {
    $links = [];
    
    // Match the section with the "Today CM free spins links" heading
    $section_pattern = '/<h2>Today CM free spins links - <span id="currentDate">(.*?)<\/span><\/h2>(.*?)(?=<h[23]|$)/si';
    if (preg_match($section_pattern, $html, $matches)) {
        $date = $matches[1];
        
        // Check each configured link pattern
        foreach ($this->post_configs[387]['link_patterns'] as $pattern) {
            // Extract href links from the matched section that match the pattern
            $link_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)"[^>]*>/i';
            preg_match_all($link_pattern, $matches[2], $link_matches);
            
            if (!empty($link_matches[1])) {
                $links = array_merge($links, $link_matches[1]);
            }
        }
    }
    
    $links = array_unique($links); // Remove duplicates
    custom_log("Fetched " . count($links) . " links for Coin Master from crazyashwin.com: " . implode(', ', $links));
    return $links;
}
    
    private function extract_board_kings_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[230]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Board Kings from mosttechs.com: " . implode(', ', $links));
        return $links;
    }
    
    private function extract_zynga_poker_links($html) {
        $links = [];
        $date_pattern = date($this->date_formats['dot']); // Using dot format: "1.11.2024"
        
        foreach ($this->post_configs[271]['link_patterns'] as $pattern) {
            $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?' . 
                preg_quote($date_pattern, '/') . '/i';
            
            preg_match_all($regex_pattern, $html, $matches);
            if (!empty($matches[1])) {
                $links = array_merge($links, $matches[1]);
            }
        }
        
        $links = array_unique($links); // Remove duplicates
        custom_log("Fetched " . count($links) . " links for Zynga Poker from mosttechs.com: " . implode(', ', $links));
        return $links;
    }

    private function extract_match_masters_links($html) {
    $links = [];
    foreach ($this->post_configs[297]['link_patterns'] as $pattern) {
        $regex_pattern = '/<a\s+href="(' . preg_quote($pattern, '/') . '[^"]+)".*?>.*?(' . 
            preg_quote(date($this->date_formats['dot']), '/') . '|' . 
            preg_quote(date($this->date_formats['ordinal']), '/') . ')/i';
        
        preg_match_all($regex_pattern, $html, $matches);
        if (!empty($matches[1])) {
            $links = array_merge($links, $matches[1]);
        }
    }
    
    $links = array_unique($links); // Remove duplicates
    custom_log("Fetched " . count($links) . " links for Match Masters from mosttechs.com: " . implode(', ', $links));
    return $links;
}

private function extract_hit_it_rich_links($html) {
    $date_pattern = '<span id="Hit_It_Rich_Todays_Free_Coins_Link-_' . date($this->date_formats['underscore']) . '">';
    $pos = strpos($html, $date_pattern);
    
    if ($pos === false) {
        custom_log("Could not find section with today's date pattern", 'warning');
        return [];
    }

    $section_start = $pos;
    $next_h4_pos = strpos($html, '<h4', $pos + strlen($date_pattern));
    $section_end = $next_h4_pos !== false ? $next_h4_pos : strlen($html);
    $section_content = substr($html, $section_start, $section_end - $section_start);

    $links = [];
    foreach ($this->post_configs[419]['link_patterns'] as $pattern) {
        preg_match_all('/<a href="(' . preg_quote($pattern, '/') . '[^"]+)"[^>]*>/i', 
            $section_content, $matches);
        
        if (!empty($matches[1])) {
            $links = array_merge($links, $matches[1]);
        }
    }
    
    $links = array_unique($links);
    $links = array_reverse($links);
    
    custom_log("Fetched " . count($links) . " links from rezortricks.com: " . implode(', ', $links));
    return $links;
}

   private function update_post_content($content, $today_heading, $links, $config) {
    $today_date = date($this->date_formats['display']);
    $content_modified = false;
    
    // Check if today's heading exists
    if (preg_match('/<h4[^>]*>.*?' . preg_quote($today_date, '/') . '.*?<\/h4>/s', $content, $heading_matches, PREG_OFFSET_CAPTURE)) {
        $heading_pos = $heading_matches[0][1];
        $heading_end = $heading_pos + strlen($heading_matches[0][0]);
        
        $next_heading_pos = stripos($content, '<h4', $heading_end);
        $section_end = $next_heading_pos !== false ? $next_heading_pos : strlen($content);
        
        $current_section = substr($content, $heading_pos, $section_end - $heading_pos);
        
        // Extract existing links from the current section
        $existing_links = array();
        foreach ($config['link_patterns'] as $pattern) {
            if (preg_match_all('/<a href="(' . preg_quote($pattern, '/') . '[^"]+)"/i', $current_section, $existing_matches)) {
                $existing_links = array_merge($existing_links, $existing_matches[1]);
            }
        }
        
        // Find new unique links
        $new_links = array_diff($links, $existing_links);
        
        if (!empty($new_links)) {
            // Combine new links (at the top) with existing links
            $all_links = array_merge($new_links, $existing_links);
            
            // Remove the existing ordered list
            $current_section = preg_replace('/<ol>.*?<\/ol>/s', '', $current_section);
            
            // Generate new ordered list with combined links
            $updated_section = $current_section . $this->generate_links_html($all_links, $config['link_text']);
            
            $content = substr_replace($content, $updated_section, $heading_pos, $section_end - $heading_pos);
            custom_log("Added " . count($new_links) . " new unique links to top of existing section for $today_date");
            $content_modified = true;
        } else {
            custom_log("No new unique links found for $today_date", 'info');
        }
    } else {
        // No existing section for today, create a new one
        if (preg_match('/<h4[^>]*>/', $content, $matches, PREG_OFFSET_CAPTURE)) {
            $first_h4_pos = $matches[0][1];
            $new_section = $today_heading . "\n" . $this->generate_links_html($links, $config['link_text']);
            $content = substr_replace($content, $new_section, $first_h4_pos, 0);
            custom_log("Created new section for $today_date at the top");
            $content_modified = true;
        } else {
            $content = $today_heading . "\n" . $this->generate_links_html($links, $config['link_text']) . $content;
            custom_log("No existing headings found, added new section at the beginning");
            $content_modified = true;
        }
    }
    
    return ['content' => $content, 'modified' => $content_modified];
}
    private function generate_heading($date) {
        return sprintf(
            '<h4 class="wp-block-heading has-text-color has-link-color wp-elements-f2ac3daac33216e856b046520ec53ee3" style="color:#008effe6">' .
            '<span class="ez-toc-section" id="%s" ez-toc-data-id="#%s"></span>' .
            '<strong>%s</strong>' .
            '<span class="ez-toc-section-end"></span>' .
            '</h4>',
            str_replace(' ', '_', $date),
            str_replace(' ', '_', $date),
            $date
        );
    }

    private function generate_links_html($links, $link_text) {
        if (empty($links)) {
            return '';
        }
        
        $html = "<ol>\n";
        foreach ($links as $link) {
            $html .= sprintf(
                '<li><a href="%s" target="_blank" rel="noopener"><strong>%s</strong></a></li>' . "\n",
                esc_url($link),
                $link_text
            );
        }
        return $html . "</ol>\n";
    }
    
    // Helper method to validate configuration
private function validate_config($config) {
    $required_fields = ['url', 'type', 'link_patterns', 'link_text'];
    foreach ($required_fields as $field) {
        if (!isset($config[$field])) {
            custom_log("Missing required configuration field: $field", 'error');
            return false;
        }
    }
    
    if (!is_array($config['link_patterns']) || empty($config['link_patterns'])) {
        custom_log("link_patterns must be a non-empty array", 'error');
        return false;
    }
    
    return true;
}

    public function run_updates() {
    $updates_made = false;
    
    foreach ($this->post_configs as $post_id => $config) {
        // Validate configuration
        if (!$this->validate_config($config)) {
            custom_log("Invalid configuration for post ID $post_id. Skipping.", 'error');
            continue;
        }
        
        $post = get_post($post_id);
        if (!$post) {
            custom_log("Post with ID $post_id not found.", 'error');
            continue;
        }

        // Generate today's heading regardless of link status
        $today_heading = $this->generate_heading(date($this->date_formats['display']));
        
        // Get links from source
        $links = $this->get_links_from_source($config['url'], $config);
        
        if (empty($links)) {
            custom_log("No links found for Post ID $post_id from source {$config['url']}.", 'warning');
            // Still proceed with empty links array to update heading
        }
        
        $update_result = $this->update_post_content($post->post_content, $today_heading, $links, $config);
        
        if ($update_result['modified']) {
            $post_update = wp_update_post([
                'ID' => $post_id,
                'post_content' => $update_result['content'],
            ]);
            
            if (is_wp_error($post_update)) {
                custom_log("Failed to update post $post_id: " . $post_update->get_error_message(), 'error');
            } else {
                custom_log("Successfully updated post $post_id");
                $updates_made = true;
            }
        }
    }
    
    if ($updates_made) {
        update_option('link_updater_last_run', current_time('mysql'));
    } else {
        custom_log("No updates needed. All posts are up to date.", 'info');
    }
}
  // Usage
public function daily_update_links() {
    try {
        // Initialize the PostLinkUpdater class
        $updater = new PostLinkUpdater();
        
        // Run the updates
        $updater->run_updates();
        
    } catch (Exception $e) {
        // Log the error message
        custom_log('Error in daily_update_links: ' . $e->getMessage());
        
    }
}


}
