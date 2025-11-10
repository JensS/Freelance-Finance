<?php

namespace App\Livewire\Quotes;

use App\Services\DocumentParser;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
#[Title('Angebote Importieren')]
class Import extends Component
{
    use WithFileUploads;

    public $files = [];

    public $importResults = [];

    public $isProcessing = false;

    protected $rules = [
        'files.*' => 'required|mimes:pdf|max:10240', // 10MB max per file
    ];

    public function updatedFiles()
    {
        $this->validate();
    }

    public function import()
    {
        $this->validate();

        if (empty($this->files)) {
            session()->flash('error', 'Bitte wählen Sie mindestens eine PDF-Datei aus.');

            return;
        }

        $this->isProcessing = true;
        $this->importResults = [];

        $parser = new DocumentParser;

        foreach ($this->files as $file) {
            try {
                $filename = $file->getClientOriginalName();
                $tempPath = $file->store('temp', 'local');
                $fullPath = storage_path('app/'.$tempPath);

                // Parse the document
                $parsedData = $parser->parseDocument($fullPath);

                if (isset($parsedData['error'])) {
                    $this->importResults[] = [
                        'filename' => $filename,
                        'status' => 'error',
                        'message' => 'Parsing error: '.$parsedData['error'],
                    ];

                    continue;
                }

                // Check if it's a quote
                if ($parsedData['type'] !== 'quote') {
                    $this->importResults[] = [
                        'filename' => $filename,
                        'status' => 'error',
                        'message' => 'Document is not a quote',
                    ];

                    continue;
                }

                // Import the quote
                $importResult = $parser->importDocument($parsedData, 'quote');

                if (isset($importResult['error'])) {
                    $this->importResults[] = [
                        'filename' => $filename,
                        'status' => 'error',
                        'message' => 'Import error: '.$importResult['error'],
                    ];
                } else {
                    $this->importResults[] = [
                        'filename' => $filename,
                        'status' => 'success',
                        'message' => 'Angebot erfolgreich importiert',
                        'quote_number' => $importResult['quote_number'],
                    ];
                }

                // Clean up temp file
                unlink($fullPath);

            } catch (\Exception $e) {
                Log::error('Quote import error', [
                    'filename' => $filename ?? 'unknown',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $this->importResults[] = [
                    'filename' => $filename ?? 'unknown',
                    'status' => 'error',
                    'message' => 'Error: '.$e->getMessage(),
                ];
            }
        }

        $this->isProcessing = false;
        session()->flash('success', count($this->files).' Dateien wurden verarbeitet.');

        // Clear files after processing
        $this->files = [];
    }

    public function removeFile($index)
    {
        unset($this->files[$index]);
        $this->files = array_values($this->files);
    }

    public function render()
    {
        return view('livewire.quotes.import');
    }
}