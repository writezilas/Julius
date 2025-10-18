<?php

/**
 * CSS Usage Analyzer for Autobidder Project
 * This script analyzes CSS files and template files to identify unused CSS rules
 */

class CSSAnalyzer {
    private $projectRoot;
    private $cssFiles = [];
    private $templateFiles = [];
    private $definedSelectors = [];
    private $usedClasses = [];
    private $usedIds = [];
    private $report = [];

    public function __construct($projectRoot) {
        $this->projectRoot = $projectRoot;
    }

    /**
     * Find all CSS files in the project
     */
    public function findCSSFiles() {
        $cssFiles = [];
        
        // Main CSS directories to scan
        $directories = [
            '/public/assets/css',
            '/resources/css', 
            '/resources/sass',
            '/resources/scss'
        ];

        foreach ($directories as $dir) {
            $fullPath = $this->projectRoot . $dir;
            if (is_dir($fullPath)) {
                $cssFiles = array_merge($cssFiles, $this->scanDirectory($fullPath, ['css', 'scss', 'sass']));
            }
        }

        // Filter out third-party libraries and minified files for custom analysis
        $customCSSFiles = array_filter($cssFiles, function($file) {
            $filename = basename($file);
            
            // Skip third-party libraries and frameworks
            $skipPatterns = [
                'bootstrap',
                'jquery',
                'fontawesome', 
                'aos',
                'swiper',
                'choices',
                'dragula',
                'dropzone',
                'filepond',
                'flatpickr',
                'fullcalendar',
                'glightbox',
                'gridjs',
                'jsvectormap',
                'leaflet',
                'multi.js',
                'nouislider',
                'prismjs',
                'quill',
                'libs/'
            ];

            foreach ($skipPatterns as $pattern) {
                if (strpos($file, $pattern) !== false) {
                    return false;
                }
            }

            return true;
        });

        $this->cssFiles = $customCSSFiles;
        return $this->cssFiles;
    }

    /**
     * Find all template files (blade, html, php)
     */
    public function findTemplateFiles() {
        $templateFiles = [];
        
        $directories = [
            '/resources/views',
            '/', // root for standalone files
            '/app/Http/Controllers' // for inline HTML in controllers
        ];

        foreach ($directories as $dir) {
            $fullPath = $this->projectRoot . $dir;
            if (is_dir($fullPath)) {
                $templateFiles = array_merge($templateFiles, $this->scanDirectory($fullPath, ['blade.php', 'php', 'html']));
            }
        }

        $this->templateFiles = $templateFiles;
        return $this->templateFiles;
    }

    /**
     * Scan directory for files with specific extensions
     */
    private function scanDirectory($directory, $extensions) {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $filename = $file->getFilename();
                foreach ($extensions as $ext) {
                    if (preg_match('/\.' . preg_quote($ext, '/') . '$/', $filename)) {
                        $files[] = $file->getPathname();
                        break;
                    }
                }
            }
        }

        return $files;
    }

    /**
     * Extract CSS selectors from CSS files
     */
    public function extractCSSSelectors() {
        $selectors = [];

        foreach ($this->cssFiles as $cssFile) {
            $content = file_get_contents($cssFile);
            
            // Remove comments
            $content = preg_replace('/\/\*.*?\*\//s', '', $content);
            
            // Extract selectors (simple approach for demonstration)
            preg_match_all('/([^{}]+)\s*\{[^{}]*\}/', $content, $matches);
            
            if (!empty($matches[1])) {
                foreach ($matches[1] as $selectorGroup) {
                    // Split multiple selectors separated by commas
                    $individualSelectors = explode(',', $selectorGroup);
                    
                    foreach ($individualSelectors as $selector) {
                        $selector = trim($selector);
                        if (!empty($selector)) {
                            if (!isset($selectors[$cssFile])) {
                                $selectors[$cssFile] = [];
                            }
                            $selectors[$cssFile][] = $selector;
                        }
                    }
                }
            }
        }

        $this->definedSelectors = $selectors;
        return $this->definedSelectors;
    }

    /**
     * Extract used classes and IDs from template files
     */
    public function extractUsedClasses() {
        $usedClasses = [];
        $usedIds = [];

        foreach ($this->templateFiles as $templateFile) {
            $content = file_get_contents($templateFile);
            
            // Extract classes from class attributes
            preg_match_all('/class=["\'](.*?)["\']/i', $content, $classMatches);
            if (!empty($classMatches[1])) {
                foreach ($classMatches[1] as $classList) {
                    $classes = preg_split('/\s+/', trim($classList));
                    foreach ($classes as $class) {
                        if (!empty($class)) {
                            $usedClasses[$class] = true;
                        }
                    }
                }
            }

            // Extract IDs from id attributes
            preg_match_all('/id=["\'](.*?)["\']/i', $content, $idMatches);
            if (!empty($idMatches[1])) {
                foreach ($idMatches[1] as $idValue) {
                    $usedIds[$idValue] = true;
                }
            }

            // Extract classes used in JavaScript (common patterns)
            preg_match_all('/[\'"]([a-zA-Z][\w-]*)[\'"]/', $content, $jsMatches);
            if (!empty($jsMatches[1])) {
                foreach ($jsMatches[1] as $possibleClass) {
                    if (preg_match('/^[a-z][\w-]*$/', $possibleClass)) {
                        $usedClasses[$possibleClass] = true;
                    }
                }
            }
        }

        $this->usedClasses = array_keys($usedClasses);
        $this->usedIds = array_keys($usedIds);
        
        return [
            'classes' => $this->usedClasses,
            'ids' => $this->usedIds
        ];
    }

    /**
     * Analyze unused CSS rules
     */
    public function analyzeUnusedCSS() {
        $unusedRules = [];
        
        foreach ($this->definedSelectors as $cssFile => $selectors) {
            $unusedRules[$cssFile] = [];
            
            foreach ($selectors as $selector) {
                $isUsed = $this->isSelectorUsed($selector);
                
                if (!$isUsed) {
                    $unusedRules[$cssFile][] = $selector;
                }
            }
        }

        return $unusedRules;
    }

    /**
     * Check if a CSS selector is used
     */
    private function isSelectorUsed($selector) {
        // Clean the selector
        $selector = trim($selector);
        
        // Skip pseudo-classes, media queries, and complex selectors for now
        if (strpos($selector, '@') === 0 || 
            strpos($selector, ':') !== false && strpos($selector, '::') === false ||
            strpos($selector, '[') !== false ||
            preg_match('/^\s*\*\s*$/', $selector)) {
            return true; // Assume these are used
        }

        // Extract class names from selector
        preg_match_all('/\.([a-zA-Z][\w-]*)/', $selector, $classMatches);
        if (!empty($classMatches[1])) {
            foreach ($classMatches[1] as $className) {
                if (in_array($className, $this->usedClasses)) {
                    return true;
                }
            }
        }

        // Extract ID names from selector
        preg_match_all('/#([a-zA-Z][\w-]*)/', $selector, $idMatches);
        if (!empty($idMatches[1])) {
            foreach ($idMatches[1] as $idName) {
                if (in_array($idName, $this->usedIds)) {
                    return true;
                }
            }
        }

        // Extract element names
        preg_match_all('/^([a-zA-Z]+)/', $selector, $elementMatches);
        if (!empty($elementMatches[1])) {
            // Common HTML elements are usually used
            $commonElements = ['html', 'body', 'div', 'span', 'p', 'a', 'img', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'li', 'table', 'tr', 'td', 'th', 'form', 'input', 'button', 'select', 'textarea'];
            if (in_array(strtolower($elementMatches[1][0]), $commonElements)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate analysis report
     */
    public function generateReport() {
        echo "=== CSS Usage Analysis Report ===\n\n";
        
        echo "Found CSS Files: " . count($this->cssFiles) . "\n";
        foreach ($this->cssFiles as $file) {
            echo "  - " . str_replace($this->projectRoot, '', $file) . "\n";
        }
        
        echo "\nFound Template Files: " . count($this->templateFiles) . "\n";
        echo "Total CSS Selectors Found: " . array_sum(array_map('count', $this->definedSelectors)) . "\n";
        echo "Total Unique Classes Used: " . count($this->usedClasses) . "\n";
        echo "Total Unique IDs Used: " . count($this->usedIds) . "\n\n";

        $unusedRules = $this->analyzeUnusedCSS();
        
        foreach ($unusedRules as $cssFile => $unused) {
            if (!empty($unused)) {
                echo "=== Potentially Unused Rules in " . basename($cssFile) . " ===\n";
                foreach ($unused as $selector) {
                    echo "  - " . $selector . "\n";
                }
                echo "\n";
            }
        }

        return $unusedRules;
    }

    /**
     * Create optimized CSS files
     */
    public function createOptimizedCSS($unusedRules) {
        foreach ($unusedRules as $cssFile => $unused) {
            if (empty($unused)) continue;

            $content = file_get_contents($cssFile);
            $optimizedContent = $content;
            
            // Create backup
            $backupFile = $cssFile . '.backup.' . date('Y-m-d-H-i-s');
            copy($cssFile, $backupFile);
            echo "Backup created: " . basename($backupFile) . "\n";
            
            // For now, just create a report file rather than actually removing rules
            // This is safer and allows manual review
            $reportFile = dirname($cssFile) . '/' . basename($cssFile, '.css') . '-optimization-report.txt';
            
            $reportContent = "CSS Optimization Report for " . basename($cssFile) . "\n";
            $reportContent .= "Generated on: " . date('Y-m-d H:i:s') . "\n\n";
            $reportContent .= "Potentially unused selectors:\n\n";
            
            foreach ($unused as $selector) {
                $reportContent .= "- " . $selector . "\n";
            }
            
            file_put_contents($reportFile, $reportContent);
            echo "Optimization report created: " . basename($reportFile) . "\n";
        }
    }

    /**
     * Run complete analysis
     */
    public function analyze() {
        echo "Starting CSS analysis...\n\n";
        
        $this->findCSSFiles();
        $this->findTemplateFiles();
        $this->extractCSSSelectors();
        $this->extractUsedClasses();
        $unusedRules = $this->generateReport();
        $this->createOptimizedCSS($unusedRules);
        
        return $unusedRules;
    }
}

// Run the analyzer
$projectRoot = __DIR__;
$analyzer = new CSSAnalyzer($projectRoot);
$analyzer->analyze();

?>