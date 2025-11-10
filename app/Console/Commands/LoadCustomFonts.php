<?php

namespace App\Console\Commands;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LoadCustomFonts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:load-custom-fonts {--force : Force reload fonts even if already loaded}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load custom fonts (Fira Sans) for PDF generation';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Loading custom fonts for PDF generation...');

        $fontDir = storage_path('fonts');
        $firaSansDir = storage_path('fonts/Fira_Sans');

        // Ensure directories exist
        if (! File::exists($fontDir)) {
            File::makeDirectory($fontDir, 0755, true);
            $this->info("Created font directory: {$fontDir}");
        }

        // Check if Fira Sans fonts exist
        if (! File::exists($firaSansDir)) {
            $this->error('Fira Sans fonts not found!');
            $this->info('Please download Fira Sans fonts from https://fonts.google.com/specimen/Fira+Sans');
            $this->info("Extract them to: {$firaSansDir}");

            return self::FAILURE;
        }

        // Required font files
        $requiredFonts = [
            'FiraSans-Light.ttf' => 300,
            'FiraSans-Regular.ttf' => 'normal',
            'FiraSans-Medium.ttf' => 500,
            'FiraSans-SemiBold.ttf' => 600,
        ];

        // Check if all required fonts exist
        $missingFonts = [];
        foreach ($requiredFonts as $fontFile => $weight) {
            if (! File::exists("{$firaSansDir}/{$fontFile}")) {
                $missingFonts[] = $fontFile;
            }
        }

        if (! empty($missingFonts)) {
            $this->error('Missing required font files:');
            foreach ($missingFonts as $font) {
                $this->line("  - {$font}");
            }

            return self::FAILURE;
        }

        $this->info('All required Fira Sans font files found.');

        // Load fonts into dompdf
        $this->loadFontsIntoDompdf($firaSansDir, $requiredFonts);

        // Display status
        $this->displayFontStatus();

        $this->newLine();
        $this->info('✓ Custom fonts loaded successfully!');

        return self::SUCCESS;
    }

    /**
     * Load fonts into dompdf font cache
     */
    protected function loadFontsIntoDompdf(string $firaSansDir, array $fonts): void
    {
        $this->info('Registering fonts with dompdf...');

        $options = new Options;
        $options->set('fontDir', storage_path('fonts'));
        $options->set('fontCache', storage_path('fonts'));
        $options->set('isRemoteEnabled', false);
        $options->set('chroot', base_path());

        $dompdf = new Dompdf($options);
        $fontMetrics = $dompdf->getFontMetrics();

        foreach ($fonts as $fontFile => $weight) {
            $fontPath = "{$firaSansDir}/{$fontFile}";
            $fontName = 'Fira Sans';

            try {
                // Register font with dompdf
                $fontMetrics->registerFont(
                    [
                        'family' => $fontName,
                        'style' => 'normal',
                        'weight' => $weight,
                    ],
                    $fontPath
                );

                $this->line("  ✓ Loaded {$fontFile} (weight: {$weight})");
            } catch (\Exception $e) {
                $this->warn("  ! Could not load {$fontFile}: ".$e->getMessage());
            }
        }
    }

    /**
     * Display current font status
     */
    protected function displayFontStatus(): void
    {
        $installedFontsFile = storage_path('fonts/installed-fonts.json');

        if (File::exists($installedFontsFile)) {
            $this->newLine();
            $this->info('Currently installed fonts:');

            $installedFonts = json_decode(File::get($installedFontsFile), true);

            if (isset($installedFonts['fira sans'])) {
                $this->line('  Fira Sans:');
                foreach ($installedFonts['fira sans'] as $weight => $path) {
                    $this->line("    - Weight {$weight}: ✓");
                }
            } else {
                $this->warn('  Fira Sans not found in font registry');
            }
        }

        // Check for font cache files
        $cacheFiles = File::glob(storage_path('fonts/fira_sans_*.ufm.json'));
        if (! empty($cacheFiles)) {
            $this->newLine();
            $this->info('Font cache files: '.count($cacheFiles).' found');
        }
    }
}
