FROM node:18

WORKDIR /usr/src/app

# Copy package.json and package-lock.json if available
COPY package*.json ./

# Install all dependencies (not just prod, since socket.io often gets installed as dev by mistake)
RUN npm install

# Copy the rest of the code
COPY . .

EXPOSE 3000

CMD ["node", "server.js"]
