/* 

******** PRWS MANAGER ********

메시지 속성
channel, deleted, id, type, content, author, member, pinned, tts

기능 msg.reply : 멘션함
msg.channel.send : 멘션없음

*/
//const config = require('./config.json');

const Discord = require("discord.js");
const request = require(request");
const requestp = require(request-promise");
const PREFIX = "++"
const token = 'YOUR TOKEN HERE'
const client = new Discord.Client();

client.on("ready", () => { //봇이 준비되었을때
        console.log('PRWS ready.'); //콘솔에 준비되었다고 띄우고
        client.user.setActivity('PRWS Discord Manager', { type: "PLAYING" });
    })

client.on('message', msg => {
    if (msg.author.equals(client.user) || msg.author.bot) return; //봇이면 무시
    if (!msg.content.startsWith(PREFIX)) return; //만약 메시지가 내가 정한 접두사로 시작하지 않는다면 무시
    var args = msg.content.substring(PREFIX.length).split(" ") // 띄어쓰기로 쪼개기

    switch (args[0].toLowerCase()) {
        case "안녕": // 명령어 감지
            msg.channel.send("> 안녕하세요.")
            break; // 실행중단

        case "핑": // 핑 임베드 연습
            msg.channel.send({
                "embed": {
                    title: `현재 핑: ${client.ping} ms`,
                }
            });
            break;

            //명령어 보내기   
            ssh.execCommand(args[1], {}).then(function(result) {
                msg.channel.send('결과: ' + result.stdout);
                msg.channel.send('에러: ' + result.stderr);
                ssh.dispose(); //커넥션 종료

            });
            break;

        case "나무":
            if (!args[1]) msg.channel.send('> 문서명을 입력해주세요.');
            if (!args[1]) return;
            msg.channel.send('> https://namu.wiki/w/' + args[1]);
            break;

        case "야":
            if (msg.author == '466176598651961344') msg.reply('예, 각하');
            if (msg.author != '466176598651961344') msg.reply('뭐 임마');
            break;

        case "아이디":
            msg.reply(msg.author);
            break;

        case "이미지":
            if (!args[1]) msg.channel.send('> 사진파일 이름을 입력해주세요.');
            if (!args[1]) return;

            const imgembed = {
                color: 0x0099ff,
                title: args[0],
                image: {
                    url: 'https://i.prws.kr/' + args[1],
                },
            };

            msg.channel.send({ embed: imgembed });
            break;

        case '학교검색':
            if (!args[1] || !args[2]) {
                msg.channel.send('> 지역과 학교 이름을 모두 입력해주세요. \n사용법: ++학교검색 {지역명} {학교이름}. \n지역명은 두글자 약칭으로 입력');
                return;
            }
            p = () => new Promise((resolve, reject) => request.get(api_url + "?Name=" + args[1] + '&Region=' + args[2], {
                headers: {
                    'User-Agent': 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Mobile/13B143',
                    'Content-Type': 'application/json',
                    'Host': 'api.prws.kr'
                },
            }, function(err, res, body) {
                if (err) reject(err);
                resolve(body);
            }));
            const schoolembed = {
                color: 0x0099ff,
                title: '검색 결과',
                description: args[2] + '에 대한 검색 결과',
                fields: [{
                        name: '학교코드',
                        value: p.SchoolCode,
                    },
                    {
                        name: '설립일',
                        value: p.FoundYmd,
                        inline: true,
                    },
                    {
                        name: '고교종류',
                        value: p.HighSchoolType,
                        inline: true,
                    },
                    {
                        name: '설립형태',
                        value: p.FoundType,
                        inline: true,
                    },
                    {
                        name: '운영형태',
                        value: p.OperationType,
                        inline: true,
                    },
                    {
                        name: '성별',
                        value: p.SchoolGender,
                        inline: true,
                    },
                    {
                        name: '\u200B',
                        value: '\u200B',
                    },
                    {
                        name: 'Powered by PRWS.kr',
                        value: 'PRWS에서 더 많은 서비스를 누려보세요!',
                        inline: true,
                    },
                ],
                timestamp: new Date(),
                footer: {
                    text: 'PRWS Discord Manager',
                    icon_url: 'https://prws.kr/favicon.ico',
                },
            };

            msg.channel.send({ embed: schoolembed });
            break;
        case '':
            break;

        default:
            const helpembed = new Discord.MessageEmbed()
                .setColor('#0099ff')
                .setTitle('도움말')
                .setURL('https://prws.kr/')
                .setAuthor('PRWS Discord Manager', 'https://prws.kr/favicon.ico', 'https://prws.kr')
                .setDescription('봇 명령어 도움말')
                .addFields({ name: '++안녕', value: '인사합니다.' }, { name: '++핑', value: '현재 핑을 알려줍니다.', inline: true }, { name: '++나무 [문서명]', value: '해당 문서의 나무위키 링크를 띄워줍니다.', inline: true }, { name: '++이미지 [파일이름.확장자]', value: 'prws.kr에 업로드 된 이미지 파일을 불러옵니다.', inline: true }, { name: '\u200B', value: '\u200B' }, { name: 'Powered by PRWS.kr', value: 'PRWS에서 더 많은 서비스를 누려보세요!', inline: true })
                .setTimestamp()
                .setFooter('PRWS Discord Manager', 'https://prws.kr/favicon.ico');

            msg.channel.send(helpembed);
            break;
    }



});

client.login(token)
