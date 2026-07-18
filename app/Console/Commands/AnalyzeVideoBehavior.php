<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\VideoAnalytic;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class AnalyzeVideoBehavior extends Command
{
    protected $signature = 'app:analyze-videos';
    protected $description = 'Run AI model to detect anomalous student behavior';

    public function handle()
    {
        $this->info('Fetching video analytics data...');
        
        $data = VideoAnalytic::all();
        
        if ($data->isEmpty()) {
            $this->error('No data found to analyze.');
            return;
        }

        $jsonData = $data->toJson();
        
        $tempFile = storage_path('app/temp_ai_console_data.json');
        file_put_contents($tempFile, $jsonData);

        $this->info('Running Python AI Model...');

        // تشغيل سكربت البايثون وتمرير البيانات له
        $process = new Process(['python', base_path('ai_engine/detect_anomalies.py'), '--file', $tempFile]);
        $process->run();

        @unlink($tempFile);

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $results = json_decode($process->getOutput(), true);

        $this->info('Analysis Complete. Results:');
        
        $headers = ['ID', 'Prediction', 'Anomaly Score'];
        $rows = [];

        foreach ($results as $result) {
            $prediction = $result['anomaly_prediction'] == -1 ? '<fg=red>Anomaly (Cheater)</>' : '<fg=green>Normal</>';
            $rows[] = [$result['id'], $prediction, $result['anomaly_score']];
        }

        $this->table($headers, $rows);
    }
}
