import os


from pyrogram import Client, types
from pyrogram.raw.types import InputUser
from pyrogram.raw.functions.users.get_full_user import GetFullUser

from dotenv import load_dotenv

import pandas as pd


load_dotenv('file.env')

e = input('Введите ссылку по типу https://t.me/git_cool: ').replace(
    'https://t.me/', '').replace('t.me/', '')

chat = e
app = Client("my_account", 10522676, "a470da0ec9c4915dc466a49e35972759")


def get_about(m):
    u = app.send(GetFullUser(id=InputUser(
        user_id=m.user_id, access_hash=m.access_hash)), sleep_threshold=50.00)
    about = u.full_user.about if u.full_user.about is not None else ''
    return about


with app:
    print('Начинаем парсинг...')
    app.get_chat(chat)
    mems = []

    for mem in app.iter_chat_members(chat):
        member: types.User = mem.user
        username = member.username if member.username is not None else ''
        phone = member.phone_number if member.phone_number is not None else ''
        name = member.first_name if member.first_name is not None else ''
        last_name = member.last_name if member.last_name is not None else ''
        m = app.resolve_peer(member.id)

        about = get_about(m)

        print(f'{username} [{name} {last_name}] {phone} - {about}')
        mems.append({'user_id': member.id, 'username': username, 'phone': phone,
                    'name': name, 'last_name': last_name, 'about': about})

        
    print('Парсинг закончен')
print('Идёт загрузка в excel....Подождите немного')

print(mems)

df = pd.DataFrame(mems)
df.to_excel('members.xlsx', index=False)
print('Excel документ ГОТОВ!')
