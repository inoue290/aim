# ベースイメージとして軽量な Nginx を使用
FROM nginx:alpine

# index.html をコンテナの Nginx 配置場所にコピー
COPY index.html /usr/share/nginx/html/index.html

# コンテナのポート80を公開
EXPOSE 80

# Nginx をフォアグラウンドで実行
CMD ["nginx", "-g", "daemon off;"]
