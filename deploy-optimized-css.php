<?php

/**
 * CSS Optimization Deployment Script for Autobidder Project
 * This script helps deploy the optimized CSS files safely
 */

class CSSDeployment {
    private $projectRoot;
    private $cssDir;
    private $backupDir;

    public function __construct($projectRoot) {
        $this->projectRoot = $projectRoot;
        $this->cssDir = $projectRoot . '/public/assets/css';
        $this->backupDir = $projectRoot . '/css-backups-' . date('Y-m-d-H-i-s');
    }

    /**
     * List all available optimized CSS files
     */
    public function listOptimizedFiles() {
        $optimizedFiles = glob($this->cssDir . '/*.optimized.css');
        $originalFiles = [];

        foreach ($optimizedFiles as $file) {
            $originalName = str_replace('.optimized.css', '.css', basename($file));
            $originalPath = $this->cssDir . '/' . $originalName;
            
            if (file_exists($originalPath)) {
                $originalFiles[] = [
                    'original' => $originalPath,
                    'optimized' => $file,
                    'name' => $originalName,
                    'savings' => $this->calculateSavings($originalPath, $file)
                ];
            }
        }

        return $originalFiles;
    }

    /**
     * Calculate file size savings
     */
    private function calculateSavings($originalPath, $optimizedPath) {
        $originalSize = filesize($originalPath);
        $optimizedSize = filesize($optimizedPath);
        $savings = $originalSize - $optimizedSize;
        $savingsPercent = $originalSize > 0 ? ($savings / $originalSize) * 100 : 0;

        return [
            'original_size' => $originalSize,
            'optimized_size' => $optimizedSize,
            'savings_bytes' => $savings,
            'savings_percent' => round($savingsPercent, 2)
        ];
    }

    /**
     * Create comprehensive backup
     */
    public function createBackup() {
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }

        $files = glob($this->cssDir . '/*.css');
        $backedUpFiles = 0;

        foreach ($files as $file) {
            if (strpos($file, '.optimized.css') === false) {
                $filename = basename($file);
                $backupPath = $this->backupDir . '/' . $filename;
                
                if (copy($file, $backupPath)) {
                    $backedUpFiles++;
                }
            }
        }

        return [
            'backup_dir' => $this->backupDir,
            'files_backed_up' => $backedUpFiles
        ];
    }

    /**
     * Deploy optimized files
     */
    public function deploy($files = null) {
        $availableFiles = $this->listOptimizedFiles();
        
        if ($files === null) {
            $filesToDeploy = $availableFiles;
        } else {
            $filesToDeploy = array_filter($availableFiles, function($file) use ($files) {
                return in_array($file['name'], $files);
            });
        }

        $deployed = [];
        $errors = [];

        foreach ($filesToDeploy as $file) {
            try {
                if (copy($file['optimized'], $file['original'])) {
                    $deployed[] = $file['name'];
                } else {
                    $errors[] = "Failed to deploy " . $file['name'];
                }
            } catch (Exception $e) {
                $errors[] = "Error deploying " . $file['name'] . ": " . $e->getMessage();
            }
        }

        return [
            'deployed' => $deployed,
            'errors' => $errors
        ];
    }

    /**
     * Restore from backup
     */
    public function restore($backupDir = null) {
        if ($backupDir === null) {
            // Find the most recent backup
            $backups = glob($this->projectRoot . '/css-backups-*');
            if (empty($backups)) {
                throw new Exception("No backup directories found");
            }
            
            rsort($backups);
            $backupDir = $backups[0];
        }

        if (!is_dir($backupDir)) {
            throw new Exception("Backup directory not found: $backupDir");
        }

        $files = glob($backupDir . '/*.css');
        $restored = [];
        $errors = [];

        foreach ($files as $file) {
            $filename = basename($file);
            $targetPath = $this->cssDir . '/' . $filename;
            
            try {
                if (copy($file, $targetPath)) {
                    $restored[] = $filename;
                } else {
                    $errors[] = "Failed to restore " . $filename;
                }
            } catch (Exception $e) {
                $errors[] = "Error restoring " . $filename . ": " . $e->getMessage();
            }
        }

        return [
            'restored' => $restored,
            'errors' => $errors,
            'backup_used' => $backupDir
        ];
    }

    /**
     * Generate deployment report
     */
    public function generateReport() {
        $files = $this->listOptimizedFiles();
        $totalOriginal = 0;
        $totalOptimized = 0;

        echo "CSS Optimization Deployment Report\n";
        echo str_repeat("=", 60) . "\n";
        echo "Generated: " . date('Y-m-d H:i:s') . "\n\n";

        echo "Available Optimized Files:\n";
        echo str_repeat("-", 40) . "\n";

        foreach ($files as $file) {
            $savings = $file['savings'];
            $totalOriginal += $savings['original_size'];
            $totalOptimized += $savings['optimized_size'];

            echo "File: " . $file['name'] . "\n";
            echo "  Original:  " . number_format($savings['original_size']) . " bytes\n";
            echo "  Optimized: " . number_format($savings['optimized_size']) . " bytes\n";
            echo "  Savings:   " . number_format($savings['savings_bytes']) . " bytes ({$savings['savings_percent']}%)\n";
            echo "  Status:    " . ($savings['savings_bytes'] > 0 ? "✅ Optimized" : "⚠️  Check manually") . "\n\n";
        }

        $totalSavings = $totalOriginal - $totalOptimized;
        $totalSavingsPercent = $totalOriginal > 0 ? ($totalSavings / $totalOriginal) * 100 : 0;

        echo "Summary:\n";
        echo str_repeat("-", 40) . "\n";
        echo "Total Files:     " . count($files) . "\n";
        echo "Total Original:  " . number_format($totalOriginal) . " bytes\n";
        echo "Total Optimized: " . number_format($totalOptimized) . " bytes\n";
        echo "Total Savings:   " . number_format($totalSavings) . " bytes (" . round($totalSavingsPercent, 2) . "%)\n\n";

        return $files;
    }

    /**
     * Interactive deployment process
     */
    public function interactiveDeploy() {
        echo "🚀 CSS Optimization Deployment Tool\n\n";

        // Show current status
        $files = $this->generateReport();

        if (empty($files)) {
            echo "❌ No optimized CSS files found. Please run the optimizer first.\n";
            return;
        }

        // Confirm deployment
        echo "Do you want to proceed with deployment? (y/n): ";
        $handle = fopen("php://stdin", "r");
        $confirmation = trim(fgets($handle));
        fclose($handle);

        if (strtolower($confirmation) !== 'y') {
            echo "❌ Deployment cancelled.\n";
            return;
        }

        // Create backup
        echo "📦 Creating backup...\n";
        $backup = $this->createBackup();
        echo "✅ Backup created: " . $backup['backup_dir'] . "\n";
        echo "✅ Files backed up: " . $backup['files_backed_up'] . "\n\n";

        // Deploy files
        echo "🔄 Deploying optimized files...\n";
        $deployment = $this->deploy();

        if (!empty($deployment['deployed'])) {
            echo "✅ Successfully deployed:\n";
            foreach ($deployment['deployed'] as $file) {
                echo "   - $file\n";
            }
        }

        if (!empty($deployment['errors'])) {
            echo "❌ Errors occurred:\n";
            foreach ($deployment['errors'] as $error) {
                echo "   - $error\n";
            }
        }

        echo "\n🎉 Deployment complete!\n";
        echo "Backup location: " . $backup['backup_dir'] . "\n";
        echo "\nNext steps:\n";
        echo "1. Test your application thoroughly\n";
        echo "2. Monitor for any visual or functional issues\n";
        echo "3. If issues occur, run: php deploy-optimized-css.php --restore\n";
        echo "4. Clean up optimization files when satisfied: rm *.optimized.css\n";
    }
}

// Command line handling
$projectRoot = __DIR__;
$deployment = new CSSDeployment($projectRoot);

// Check command line arguments
$args = $argv ?? [];

if (in_array('--restore', $args)) {
    echo "🔄 Restoring from backup...\n";
    try {
        $result = $deployment->restore();
        echo "✅ Restored " . count($result['restored']) . " files\n";
        if (!empty($result['errors'])) {
            echo "❌ Errors:\n";
            foreach ($result['errors'] as $error) {
                echo "   - $error\n";
            }
        }
    } catch (Exception $e) {
        echo "❌ Restore failed: " . $e->getMessage() . "\n";
    }
} elseif (in_array('--report', $args)) {
    $deployment->generateReport();
} elseif (in_array('--help', $args)) {
    echo "CSS Optimization Deployment Tool\n";
    echo "Usage:\n";
    echo "  php deploy-optimized-css.php          - Interactive deployment\n";
    echo "  php deploy-optimized-css.php --report - Show optimization report\n";
    echo "  php deploy-optimized-css.php --restore - Restore from backup\n";
    echo "  php deploy-optimized-css.php --help   - Show this help\n";
} else {
    // Interactive mode
    $deployment->interactiveDeploy();
}

?>