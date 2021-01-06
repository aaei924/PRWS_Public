본 사이트는 아래와 같이 JSON API를 제공하고 있습니다.

API 설명
============================
ComciganString
---------------------
컴시간알리미 데이터 호출에 필요한 URL 접두어를 불러옵니다.

MCLatestForgeVersion
----------------------
최신 포지 버전을 불러옵니다.

getTimeStamp
----------------------
타임스탬프를 변환해줍니다.

API 호출 URL
=============================
ComciganString
----------------------
https://api.prws.kr/ComciganString

MCLatestForgeVersion
----------------------
https://api.prws.kr/MCLatestForgeVersion

getTimeStamp
----------------------
https://api.prws.kr/getTimeStamp

API 호출 인자
=============================
ComciganString
---------------------
1. 기본인자
없음

2. 출력인자
* inGetSchool: 컴시간 학교 검색 시 URL 접두어
* inGetData: 컴시간 시간표 검색 시 URL 접두어

MCLatestForgeVersion
----------------------
1. 기본인자
* ver: GET, MineCraft 버전

2. 출력인자
* version: 마인크래프트 버전
* Forge: 해당 마인크래프트 버전에 대응하는 Forge의 최신버전

getTimeStamp
----------------------
1. 기본인자(선택)
* TimeStamp: GET, 타임스탬프

2. 출력인자
* TimeStamp: 타임스탬프(기본인자 없을 시 현재 시각 기준 출력)
* RealTime: 타임스탬프에 대응하는 현재시각
* GreenwichMainTime: 타임스탬프에 대응하는 그리니치 표준시

API 호출 예시
=============================
ComciganString
----------------------
{"Status":"200","inGetSchool":"100823?53892l","inGetData":"100823"}

MCLatestForgeVersion
----------------------
{"Status":"200","Version":"1.5.2","Forge":"7.8.1.738"}

getTimeStamp
----------------------
{"Status":"200","TimeStamp":"1602607753","RealTime":"2020-10-14-01:49:13","GreenwichMainTime":"2020-10-13-16:49:13"}
