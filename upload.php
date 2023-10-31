<?php
// POST 요청인지 확인
if ($_SERVER['REQUEST_METHOD'] == 'POST' &&
    // 파일이 업로드 되었는지 확인
    isset($_FILES['fileToUpload']) &&
    // 업로드 중 오류가 없었는지 확인
    $_FILES['fileToUpload']['error'] == UPLOAD_ERR_OK &&
    // 실제로 업로드된 파일이 있는지 확인
    is_uploaded_file($_FILES['fileToUpload']['tmp_name'])) {

    // 원본 파일명 저장
    $original_filename = pathinfo($_FILES['fileToUpload']['name'], PATHINFO_FILENAME);
    $original_extension = pathinfo($_FILES['fileToUpload']['name'], PATHINFO_EXTENSION);

    // 업로드 디렉토리 설정
    $upload_dir = '/var/www/uploads/';

    // 임시 파일 이름을 생성 (Base64 인코딩 사용)
    $temp_filename = base64_encode($original_filename);
    // 임시 파일 이름에서 '=' 문자 제거
    $temp_filename = str_replace('=', '', $temp_filename) . '.' . $original_extension;
    $temp_filepath = $upload_dir . $temp_filename;

    // 파일 이동
    move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $temp_filepath);

    // 변환될 PDF 파일의 경로 설정
    $output_pdf_path = $upload_dir . $original_filename . '.pdf';

    // 확장자가 'hwp' 또는 'hwpx'인 경우
    if (in_array(strtolower($original_extension), ['hwp', 'hwpx'])) {
        // 임시 odt 파일 경로 설정
        $temp_odt_path = $upload_dir . $temp_filename . '.odt';
        // libreoffice 명령어 실행 (hwp 또는 hwpx를 odt로 변환)
        $command = "export HOME='$upload_dir' && libreoffice --headless --convert-to odt --outdir '$upload_dir' '$temp_filepath'";
        exec($command, $output, $return_var);

        // 변환 실패 시 오류 메시지 출력
        if ($return_var !== 0 || !file_exists($temp_odt_path)) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['StatusCode' => 500, 'message' => 'HWP or HWPX to ODT conversion failed']);
            exit;
        }
        // 변환이 성공하면, 변환된 파일 경로로 업데이트
        $temp_filepath = $temp_odt_path;
    }

    // libreoffice 명령어 실행 (odt 또는 원래 파일을 pdf로 변환)
    $command = "export HOME='$upload_dir' && libreoffice --headless --convert-to pdf --outdir '$upload_dir' '$temp_filepath'";
    exec($command, $output, $return_var);

    // PDF 변환이 성공한 경우
if ($return_var === 0 && file_exists($output_pdf_path)) {
    // PDF 파일 다운로드 헤더 설정
    header('Content-Description: File Transfer');
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $original_filename . '.pdf"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($output_pdf_path));
    // PDF 파일 출력
    readfile($output_pdf_path);

    // 스크립트 종료 후 파일 삭제를 위한 함수 등록
    register_shutdown_function(function() use ($temp_filepath, $output_pdf_path, $temp_odt_path) {
        sleep(15);
        if (file_exists($temp_filepath)) unlink($temp_filepath);
        if (file_exists($output_pdf_path)) unlink($output_pdf_path);
        if (isset($temp_odt_path) && file_exists($temp_odt_path)) unlink($temp_odt_path);
    });

    exit;
} else {
    // 변환 실패 시 오류 메시지 출력
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['StatusCode' => 500, 'message' => 'File to PDF conversion failed']);
    exit;
}
