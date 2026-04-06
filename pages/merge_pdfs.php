<?php
// 啟用錯誤報告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 檢查是否有文件上傳
if (!isset($_FILES['main_pdf']) || !isset($_FILES['jecket_pdf'])) {
    error_log("Missing PDF files in upload");
    http_response_code(400);
    echo 'Missing PDF files';
    exit;
}

try {
    // 記錄上傳的文件信息
    error_log("Main PDF: " . print_r($_FILES['main_pdf'], true));
    error_log("Jecket PDF: " . print_r($_FILES['jecket_pdf'], true));

    // 檢測環境並設置相應的路徑
    $isXAMPP = strpos($_SERVER['DOCUMENT_ROOT'], 'XAMPP') !== false;

    if ($isXAMPP) {
        // XAMPP 環境
        $tempDir = '/Applications/XAMPP/xamppfiles/temp/pdf_merge_' . uniqid();
        $pdftkPath = '/opt/homebrew/bin/pdftk';  // macOS 上的 pdftk 路徑

        // 確保 XAMPP 臨時目錄存在
        if (!file_exists('/Applications/XAMPP/xamppfiles/temp')) {
            mkdir('/Applications/XAMPP/xamppfiles/temp', 0777, true);
        }
    } else {
        // 雲服務器環境
        $tempDir = '/var/tmp/pdf_merge_' . uniqid();
        $pdftkPath = '/usr/bin/pdftk';  // Linux 上的 pdftk 路徑

        // 確保雲服務器臨時目錄存在
        if (!file_exists('/var/tmp/pdf_merge')) {
            mkdir('/var/tmp/pdf_merge', 0777, true);
        }
    }

    error_log("Creating temp directory: " . $tempDir);

    if (!mkdir($tempDir, 0777, true)) {
        error_log("Failed to create temp directory: " . $tempDir);
        throw new Exception('Failed to create temporary directory');
    }

    // 保存上傳的文件
    $mainPdfPath = $tempDir . '/main.pdf';
    $jecketPdfPath = $tempDir . '/jecket.pdf';
    $outputPath = $tempDir . '/merged.pdf';

    error_log("Moving main PDF to: " . $mainPdfPath);
    if (!move_uploaded_file($_FILES['main_pdf']['tmp_name'], $mainPdfPath)) {
        error_log("Failed to move main PDF. Error: " . error_get_last()['message']);
        throw new Exception('Failed to save main PDF');
    }

    error_log("Moving jecket PDF to: " . $jecketPdfPath);
    if (!move_uploaded_file($_FILES['jecket_pdf']['tmp_name'], $jecketPdfPath)) {
        error_log("Failed to move jecket PDF. Error: " . error_get_last()['message']);
        throw new Exception('Failed to save jecket PDF');
    }

    // 設置文件權限
    chmod($mainPdfPath, 0666);
    chmod($jecketPdfPath, 0666);

    // 檢查文件是否存在
    error_log("Checking if files exist:");
    error_log("Main PDF exists: " . (file_exists($mainPdfPath) ? 'Yes' : 'No'));
    error_log("Jecket PDF exists: " . (file_exists($jecketPdfPath) ? 'Yes' : 'No'));

    if (!file_exists($mainPdfPath) || !file_exists($jecketPdfPath)) {
        throw new Exception('PDF files not found after saving');
    }

    // 檢查文件權限
    error_log("File permissions:");
    error_log("Main PDF: " . substr(sprintf('%o', fileperms($mainPdfPath)), -4));
    error_log("Jecket PDF: " . substr(sprintf('%o', fileperms($jecketPdfPath)), -4));

    // 檢查 pdftk 是否可用
    error_log("Checking pdftk at: " . $pdftkPath);

    if (!file_exists($pdftkPath)) {
        error_log("pdftk not found at: " . $pdftkPath);
        throw new Exception('pdftk command not found');
    }

    // 檢查 pdftk 版本
    exec($pdftkPath . ' --version', $versionOutput, $versionReturn);
    error_log("pdftk version: " . implode("\n", $versionOutput));

    // 構建並執行 pdftk 命令
    $command = escapeshellcmd($pdftkPath) . ' ' .
        escapeshellarg($mainPdfPath) . ' ' .
        escapeshellarg($jecketPdfPath) . ' ' .
        'cat output ' .
        escapeshellarg($outputPath);

    error_log("Executing command: " . $command);

    // 執行命令並捕獲輸出
    exec($command . ' 2>&1', $output, $returnVar);

    error_log("Command output: " . implode("\n", $output));
    error_log("Return value: " . $returnVar);

    if ($returnVar !== 0) {
        error_log("PDF merge error: " . implode("\n", $output));
        throw new Exception('Failed to merge PDFs using pdftk. Check error log for details.');
    }

    // 檢查輸出文件
    error_log("Checking output file: " . $outputPath);
    if (!file_exists($outputPath)) {
        error_log("Output file not created");
        throw new Exception('Output PDF file not created');
    }

    // 設置輸出文件權限
    chmod($outputPath, 0666);

    error_log("Output file size: " . filesize($outputPath) . " bytes");

    // 輸出合併後的 PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="merged.pdf"');
    header('Content-Length: ' . filesize($outputPath));

    readfile($outputPath);

    // 清理臨時文件
    error_log("Cleaning up temporary files");
    @unlink($mainPdfPath);
    @unlink($jecketPdfPath);
    @unlink($outputPath);
    @rmdir($tempDir);

    error_log("PDF merge completed successfully");
} catch (Exception $e) {
    error_log("PDF merge exception: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo 'Error merging PDFs: ' . $e->getMessage();
}
