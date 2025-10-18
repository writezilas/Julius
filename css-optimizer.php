<?php

/**
 * Advanced CSS Optimizer for Autobidder Project
 * This script creates optimized versions of CSS files by removing unused rules
 */

class CSSOptimizer {
    private $projectRoot;
    private $config;
    
    public function __construct($projectRoot) {
        $this->projectRoot = $projectRoot;
        $this->config = [
            'preserve_important_selectors' => true,
            'preserve_media_queries' => true,
            'preserve_pseudo_selectors' => true,
            'preserve_keyframes' => true,
            'backup_original' => true,
            'dry_run' => false
        ];
    }

    /**
     * Optimize specific CSS files based on usage analysis
     */
    public function optimizeFiles($files = null) {
        if ($files === null) {
            // Focus on custom CSS files that are more likely to have unused rules
            $files = [
                '/public/assets/css/custom.css',
                '/public/assets/css/market-timer-card.css',
                '/public/assets/css/enhanced-countdown.css',
                '/public/assets/css/enhanced-bidding-cards.css',
                '/public/assets/css/payment-form.css',
                '/public/assets/css/dashboard-enhanced.css',
                '/public/assets/css/announcement-card.css'
            ];
        }

        $results = [];
        
        foreach ($files as $file) {
            $fullPath = $this->projectRoot . $file;
            if (file_exists($fullPath)) {
                $result = $this->optimizeFile($fullPath);
                $results[$file] = $result;
            }
        }
        
        return $results;
    }

    /**
     * Optimize a single CSS file
     */
    private function optimizeFile($filePath) {
        $originalContent = file_get_contents($filePath);
        $originalSize = strlen($originalContent);
        
        // Parse CSS to identify rules and their usage
        $cssRules = $this->parseCSSRules($originalContent);
        $usedRules = $this->filterUsedRules($cssRules);
        
        // Reconstruct CSS with only used rules
        $optimizedContent = $this->reconstructCSS($usedRules);
        $optimizedSize = strlen($optimizedContent);
        
        $savings = $originalSize - $optimizedSize;
        $savingsPercent = ($savings / $originalSize) * 100;
        
        $result = [
            'original_size' => $originalSize,
            'optimized_size' => $optimizedSize,
            'savings' => $savings,
            'savings_percent' => round($savingsPercent, 2),
            'rules_removed' => count($cssRules) - count($usedRules),
            'rules_kept' => count($usedRules)
        ];
        
        // Create optimized file
        if (!$this->config['dry_run']) {
            $this->createOptimizedFile($filePath, $optimizedContent, $result);
        }
        
        return $result;
    }

    /**
     * Parse CSS content into rules
     */
    private function parseCSSRules($content) {
        $rules = [];
        
        // Remove comments first
        $content = preg_replace('/\/\*.*?\*\//s', '', $content);
        
        // Split into rules
        preg_match_all('/([^{}]+)\s*\{([^{}]*(?:\{[^{}]*\}[^{}]*)*)\}/s', $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $selector = trim($match[1]);
            $declarations = trim($match[2]);
            
            // Skip empty rules
            if (empty($selector) || empty($declarations)) {
                continue;
            }
            
            // Handle nested rules (basic support)
            if (strpos($declarations, '{') !== false) {
                // This is a nested rule (like media queries), preserve it
                $rules[] = [
                    'selector' => $selector,
                    'declarations' => $declarations,
                    'type' => 'nested',
                    'keep' => true
                ];
            } else {
                $rules[] = [
                    'selector' => $selector,
                    'declarations' => $declarations,
                    'type' => 'simple',
                    'keep' => false
                ];
            }
        }
        
        return $rules;
    }

    /**
     * Filter rules to keep only used ones
     */
    private function filterUsedRules($rules) {
        $usedRules = [];
        
        foreach ($rules as $rule) {
            $selector = $rule['selector'];
            
            // Always preserve certain types of selectors
            if ($this->shouldPreserveSelector($selector)) {
                $rule['keep'] = true;
                $usedRules[] = $rule;
                continue;
            }
            
            // Check if selector is used in templates
            if ($this->isSelectorUsedInProject($selector)) {
                $rule['keep'] = true;
                $usedRules[] = $rule;
            }
        }
        
        return $usedRules;
    }

    /**
     * Check if a selector should be preserved regardless of usage detection
     */
    private function shouldPreserveSelector($selector) {
        // Preserve media queries
        if (strpos($selector, '@media') === 0) {
            return true;
        }
        
        // Preserve keyframes
        if (strpos($selector, '@keyframes') === 0 || strpos($selector, '@-webkit-keyframes') === 0) {
            return true;
        }
        
        // Preserve font-face
        if (strpos($selector, '@font-face') === 0) {
            return true;
        }
        
        // Preserve root variables
        if (strpos($selector, ':root') !== false) {
            return true;
        }
        
        // Preserve important base selectors
        $importantSelectors = ['html', 'body', '*', '*::before', '*::after'];
        foreach ($importantSelectors as $important) {
            if (trim($selector) === $important) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if selector is used in project files
     */
    private function isSelectorUsedInProject($selector) {
        // Extract class names and IDs from selector
        $classes = $this->extractClasses($selector);
        $ids = $this->extractIds($selector);
        
        if (empty($classes) && empty($ids)) {
            // If no specific classes or IDs, assume it's used (element selectors, etc.)
            return true;
        }
        
        return $this->searchInTemplates($classes, $ids);
    }

    /**
     * Extract class names from CSS selector
     */
    private function extractClasses($selector) {
        preg_match_all('/\.([a-zA-Z][\w-]*)/', $selector, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Extract ID names from CSS selector
     */
    private function extractIds($selector) {
        preg_match_all('/#([a-zA-Z][\w-]*)/', $selector, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Search for classes and IDs in template files
     */
    private function searchInTemplates($classes, $ids) {
        $searchTerms = array_merge($classes, $ids);
        
        if (empty($searchTerms)) {
            return false;
        }
        
        // Quick search in common directories
        $searchDirs = [
            $this->projectRoot . '/resources/views',
            $this->projectRoot . '/public',
            $this->projectRoot . '/app'
        ];
        
        foreach ($searchDirs as $dir) {
            if (!is_dir($dir)) continue;
            
            foreach ($searchTerms as $term) {
                $command = "grep -r --include=\"*.php\" --include=\"*.blade.php\" --include=\"*.html\" --include=\"*.js\" \"$term\" \"$dir\" 2>/dev/null";
                $output = shell_exec($command);
                
                if (!empty($output)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Reconstruct CSS from filtered rules
     */
    private function reconstructCSS($rules) {
        $css = "/* Optimized CSS - Generated on " . date('Y-m-d H:i:s') . " */\n\n";
        
        foreach ($rules as $rule) {
            if ($rule['type'] === 'nested') {
                $css .= $rule['selector'] . " {\n" . $rule['declarations'] . "\n}\n\n";
            } else {
                $css .= $rule['selector'] . " {\n    " . str_replace(';', ";\n    ", trim($rule['declarations'])) . "\n}\n\n";
            }
        }
        
        return $css;
    }

    /**
     * Create optimized file and backup
     */
    private function createOptimizedFile($originalPath, $optimizedContent, $result) {
        $filename = basename($originalPath);
        $directory = dirname($originalPath);
        
        // Create backup
        if ($this->config['backup_original']) {
            $backupPath = $originalPath . '.backup.' . date('Y-m-d-H-i-s');
            copy($originalPath, $backupPath);
        }
        
        // Create optimized version
        $optimizedPath = $directory . '/' . str_replace('.css', '.optimized.css', $filename);
        file_put_contents($optimizedPath, $optimizedContent);
        
        // Create summary report
        $reportPath = $directory . '/' . str_replace('.css', '.optimization-summary.txt', $filename);
        $report = $this->generateSummaryReport($filename, $result);
        file_put_contents($reportPath, $report);
        
        echo "✅ Optimized: " . $filename . "\n";
        echo "   Original size: " . number_format($result['original_size']) . " bytes\n";
        echo "   Optimized size: " . number_format($result['optimized_size']) . " bytes\n";
        echo "   Savings: " . number_format($result['savings']) . " bytes ({$result['savings_percent']}%)\n";
        echo "   Rules removed: {$result['rules_removed']}\n";
        echo "   Rules kept: {$result['rules_kept']}\n\n";
    }

    /**
     * Generate summary report
     */
    private function generateSummaryReport($filename, $result) {
        $report = "CSS Optimization Summary for {$filename}\n";
        $report .= str_repeat("=", 50) . "\n";
        $report .= "Generated on: " . date('Y-m-d H:i:s') . "\n\n";
        $report .= "File Size Comparison:\n";
        $report .= "- Original size: " . number_format($result['original_size']) . " bytes\n";
        $report .= "- Optimized size: " . number_format($result['optimized_size']) . " bytes\n";
        $report .= "- Savings: " . number_format($result['savings']) . " bytes\n";
        $report .= "- Percentage saved: {$result['savings_percent']}%\n\n";
        $report .= "Rules Analysis:\n";
        $report .= "- Rules removed: {$result['rules_removed']}\n";
        $report .= "- Rules kept: {$result['rules_kept']}\n\n";
        $report .= "Note: The optimized file preserves all media queries, keyframes, \n";
        $report .= "and other critical CSS constructs. Only unused class and ID \n";
        $report .= "selectors have been removed based on usage analysis.\n";
        
        return $report;
    }

    /**
     * Set configuration options
     */
    public function setConfig($key, $value) {
        $this->config[$key] = $value;
    }

    /**
     * Run optimization with summary
     */
    public function run($files = null) {
        echo "🚀 Starting CSS optimization...\n\n";
        
        $results = $this->optimizeFiles($files);
        
        $totalOriginal = 0;
        $totalOptimized = 0;
        $totalRulesRemoved = 0;
        
        foreach ($results as $file => $result) {
            $totalOriginal += $result['original_size'];
            $totalOptimized += $result['optimized_size'];
            $totalRulesRemoved += $result['rules_removed'];
        }
        
        $totalSavings = $totalOriginal - $totalOptimized;
        $totalSavingsPercent = $totalOriginal > 0 ? ($totalSavings / $totalOriginal) * 100 : 0;
        
        echo "📊 OPTIMIZATION SUMMARY\n";
        echo str_repeat("=", 50) . "\n";
        echo "Files optimized: " . count($results) . "\n";
        echo "Total original size: " . number_format($totalOriginal) . " bytes\n";
        echo "Total optimized size: " . number_format($totalOptimized) . " bytes\n";
        echo "Total savings: " . number_format($totalSavings) . " bytes\n";
        echo "Overall savings: " . round($totalSavingsPercent, 2) . "%\n";
        echo "Total rules removed: {$totalRulesRemoved}\n\n";
        
        if (!$this->config['dry_run']) {
            echo "✅ Optimized files have been created with '.optimized.css' extension\n";
            echo "✅ Backups of original files have been created\n";
            echo "✅ Summary reports have been generated\n\n";
            echo "Next steps:\n";
            echo "1. Test your application with the optimized CSS files\n";
            echo "2. If everything works correctly, replace the original files\n";
            echo "3. Update your build process to include this optimization\n";
        }
    }
}

// Configuration
$projectRoot = __DIR__;
$optimizer = new CSSOptimizer($projectRoot);

// Set to dry run mode for testing (set to false to actually create files)
$optimizer->setConfig('dry_run', false);

// Run optimization
$optimizer->run();

?>