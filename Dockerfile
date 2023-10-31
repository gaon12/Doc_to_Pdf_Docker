# Ubuntu 20.04 버전을 기반으로 하는 새 이미지를 만듭니다.
FROM ubuntu:20.04

# 대화형 프롬프트를 비활성화하여 패키지 설치 중 사용자에게 질문이 표시되지 않도록 합니다.
ENV DEBIAN_FRONTEND=noninteractive

# 필요한 도구와 종속성을 설치합니다.
RUN apt-get update && \
    apt-get install -y wget gnupg2 software-properties-common fonts-noto && \
    add-apt-repository ppa:libreoffice/ppa && \
    apt-get update && \
    apt-get install -y libreoffice apache2 php libapache2-mod-php && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# upload.php 파일을 컨테이너의 /var/www/html 디렉토리에 복사합니다.
COPY upload.php /var/www/html/upload.php

# 확장 기능 H2Orestart.oxt를 컨테이너의 /tmp 디렉토리에 복사하고 설치합니다.
COPY H2Orestart.oxt /tmp/H2Orestart.oxt
RUN unopkg add --shared /tmp/H2Orestart.oxt && rm /tmp/H2Orestart.oxt

# 폰트를 설치합니다. 여기에는 Noto, Microsoft Core Fonts, 그리고 나눔 폰트가 포함됩니다.
RUN echo "ttf-mscorefonts-installer msttcorefonts/accepted-mscorefonts-eula select true" | debconf-set-selections && \
    apt-get update && \
    apt-get install -y language-pack-ko fonts-noto-cjk fonts-noto-cjk-extra msttcorefonts fonts-nanum* && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# LibreOffice의 폰트 설정 파일을 생성하고 수정합니다. 모든 설치된 폰트를 LibreOffice에서 사용할 수 있도록 설정합니다.
RUN mkdir -p /root/.config/libreoffice/4/user && \
    echo '\
<item oor:path="/org.openoffice.Office.Common/Misc">\
   <prop oor:name="FontSubstitution" oor:op="fuse">\
      <value>true</value>\
   </prop>\
</item>\
<item oor:path="/org.openoffice.Office.Common/Font/Substitution">\
   <prop oor:name="FontSubstitutions">\
      <value>ReplaceFonts1 ReplaceFonts2 ReplaceFonts3</value>\
   </prop>\
   <prop oor:name="ReplaceFonts1">\
      <node oor:name="Font" oor:op="fuse">\
         <prop oor:name="FontName">\
            <value>[None]</value>\
         </prop>\
         <prop oor:name="ReplaceWith">\
            <value>Noto Sans</value>\
         </prop>\
      </node>\
   </prop>\
   <prop oor:name="ReplaceFonts2">\
      <node oor:name="Font" oor:op="fuse">\
         <prop oor:name="FontName">\
            <value>[None]</value>\
         </prop>\
         <prop oor:name="ReplaceWith">\
            <value>Microsoft Sans Serif</value>\
         </prop>\
      </node>\
   </prop>\
   <prop oor:name="ReplaceFonts3">\
      <node oor:name="Font" oor:op="fuse">\
         <prop oor:name="FontName">\
            <value>[None]</value>\
         </prop>\
         <prop oor:name="ReplaceWith">\
            <value>Nanum Gothic</value>\
         </prop>\
      </node>\
   </prop>\
</item>' > /root/.config/libreoffice/4/user/registrymodifications.xcu

# Apache 웹 서버를 설정하고, 포트 80을 열어 외부에서 접근할 수 있도록 합니다.
EXPOSE 80

# Apache 웹 서버를 실행합니다.
CMD ["apachectl", "-D", "FOREGROUND"]
