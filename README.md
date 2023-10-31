# Doc_to_Pdf_Docker

## 소개

이 프로젝트는 문서 파일을 PDF 형식으로 변환하는 작업을 Docker 컨테이너 내에서 자동화하기 위한 것입니다. 이 프로젝트는 Ubuntu 20.04를 기반으로 하며, LibreOffice를 사용하여 변환 작업을 수행합니다.

## 기능

- 다양한 문서 형식을 PDF로 변환
- LibreOffice를 사용하여 높은 호환성 보장
- Docker를 이용한 환경 분리 및 자동화

## 시작하기 전에

이 프로젝트를 실행하기 위해서는 Docker와 docker-compose가 설치되어 있어야 합니다.

## 설치 방법

1. 깃허브에서 이 프로젝트를 클론합니다.

```bash
git clone https://github.com/gaon12/Doc_to_Pdf_Docker.git
```

2. Docker 이미지를 빌드하고 컨테이너를 실행합니다.

```bash
cd Doc_to_Pdf_Docker
docker-compose build
docker-compose up -d
```

## 사용 방법

1. `http://localhost:8080/upload.php`로 `POST` 요청을 보냅니다. 이때 `fileToUpload` 키에 문서 파일을 넣어 전송합니다.
2. 변환된 PDF 파일을 다운로드합니다.

## 필수 파일

`H2Orestart.oxt` 파일은 업데이트가 지속되기 때문에 본 저장소에 포함되어 있지 않습니다. 해당 파일은 [H2Orestart GitHub 저장소](https://github.com/ebandal/H2Orestart)에서 다운로드 받을 수 있습니다. 이 파일은 한글 문서(`*.hwp`, `*.hwpx`)를 처리하기 위해 필요합니다.

## 라이선스

이 프로젝트는 GPLv3 라이선스 하에 있습니다. 자세한 내용은 `LICENSE` 파일을 참조하십시오.
