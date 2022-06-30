import vk_api
import requests
import json
from time import sleep
import asyncio as aio

class API:
    add_user_url = 'https://test.xyz/api/vk/users.php'
    add_group_url = 'https://encry.xyz/api/vk/groups.php'
    headers = {
        "user-agent": "CryptoneBOT"
    }
    @classmethod
    async def add_user(self, user_id:int = None, first_name:str = None, last_name:str = None):
        data = {

        }
        if user_id:
            data['user_id'] = user_id
        if first_name:
            data['first_name'] = first_name
        if last_name:
            data['last_name'] = last_name

        resp = requests.post(self.add_user_url, data = data, headers = self.headers)
        if resp.status_code == 200:
            try:
                return resp.json()
            except:
                return {"error": "No JSON response.", "response": resp.text}
    
    @classmethod
    def add_group(self, group_id:int = None, user_id:int = None):
        data = {

        }
        if user_id:
            data['user_id'] = user_id
        if group_id:
            data['group_id'] = group_id

        resp = requests.post(self.add_user_url, data = data, headers = self.headers)
        if resp.status_code == 200:
            try:
                return resp.json()
            except:
                return {"error": "No JSON response.", "response": resp.text}

    @classmethod
    def get_user(self, user_id:int):
        resp = requests.get(self.add_user_url + f'?id={user_id}', headers = self.headers)
        if resp.status_code == 200:
            try:
                return resp.json()
            except:
                return {"error": "No JSON response.", "response": resp.text}

class UserScroller:
    def __init__(self):
        self.token = ''
        self.vk = vk_api.VkApi(token = self.token).get_api()
        self.cfg = json.load(open('users.json', 'r', encoding = 'utf-8'))
        self.users_count = 1000

    def wait_until_spawn_new_user(self):
        user_ids = []
        start_from = requests.get(API.add_user_url).json()['response']['id']
        for user_id in range((start_from), (start_from + self.users_count)):
            user_ids.append(user_id)
        users = self.vk.users.get(user_ids = user_ids)
        print("Get users success")
        if users:
            return users
        else:
            print("No users in list, waiting for new.")
            sleep(4)
            return self.wait_until_spawn_new_user()

    def scroll_users(self):
        user_ids = []
        start_from = requests.get(API.add_user_url).json()['response']['id']
        for user_id in range((start_from), (start_from + self.users_count)):
            user_ids.append(user_id)
        users = self.vk.users.get(user_ids = user_ids)        
        print("Get users success")
        if not users:
            users = self.wait_until_spawn_new_user()
       
        for user in users:
            if 'deactivated' in user:
                continue
            else:
                aio.create_task(API.add_user(user['id'], user['first_name'], user['last_name']))
        print("From", start_from , "to", start_from + self.users_count, 'user_ids successfully added')

    def run(self):
        while True:
            self.scroll_users()
            
us = UserScroller()
us.run()