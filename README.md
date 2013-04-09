opAutoFriendPlugin
======================

フレンドリンクを自動的に管理するプラグインです。

■セットアップ方法
-----
 ./symfony opPlugin:install opAutoFriendPlugin -r 1.1.1

■使い方
-----
/etc/crontab などに以下のようにcronの設定を行ないます。

0 * * * * cd /var/www/OPENPNE_DIR/ && /usr/bin/php symfony cqc.jp:AutoFriend
上記の設定では、毎時０分に全メンバーのフレンドリンクを行ないます。

symfony cqc.jp:AutoFriend --member_id=9999
id=9999 のユーザーを全フレンドリンクにする。

symfony cqc.jp:AutoFriend --community_id=9999
コミュニティID=9999 内の全メンバー同士をフレンドリンクにする。

■TODO
-----
・コミュニティ指定やフレンド指定にバグがあるので修正
・ユーザー追加イベントに合わせて、自動的にタスクを実行する
・パフォーマンスチューニング(300人制限の解除)
・片側リンクなど壊れているレコードの修復
・特定のユーザーのフレンドリンクを切るタスクの追加（フレンドにならないユーザーをつくる）
・特定のユーザーだけは手動フレンドリンクにするタスクの追加
・コミュニティ指定時のフレンドリンクがおかしい、モデルのパラメータが足りないなど

■更新情報
-----
・2013/02/19 Ver1.1.1 Beta パッケージ名をcqc.jpに変更 
・2010/07/26 ログイン停止中のメンバーとフレンドにならないようにした。
・2010/07/09 Ver1.1 Beta 指定したコミュニティ内の全メンバーとのフレンドリンクに対応した。
・2010/07/04 Ver1.0 Stable 全メンバーのリンク、個別メンバーのリンクに対応した。


■コピーライト＆免責事項
-----
このソフトウエアは手嶋守が開発しApache2.0ライセンスで公開します。
このソフトウエアを利用したいかなる損害にも、開発者は責任を負いません。
