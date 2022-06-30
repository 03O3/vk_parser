import vk_api
import requests
import json
from time import sleep

class API:
    add_user_url = 'https://domain.test/api/vk/users.php'
    add_group_url = 'https://domain.test/api/vk/groups.php'
    headers = {
        "user-agent": "CryptoneBOT"
    }
    @classmethod
    def add_user(self, user_id:int = None, first_name:str = None, last_name:str = None):
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
            data['owner_id'] = user_id
        if group_id:
            data['group_id'] = group_id
        resp = requests.post(self.add_group_url, data = data, headers = self.headers)
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

class GroupScroller:
    def __init__(self):
        self.token = ''
        self.vk = vk_api.VkApi(token = self.token).get_api()
        self.cfg = json.load(open('groups.json', 'r', encoding = 'utf-8'))
        self.count = 500


    def wait_until_spawn_new_groups(self):
        groups = self.vk.groups.getById(group_ids = [user_id for user_id in range((self.start_from), (self.start_from + self.count))])
        if groups:
            return groups
        else:
            print("No groups in list, waiting for new.")
            sleep(4)
            return self.wait_until_spawn_new_groups()


    def scroll_groups(self):
        print("gr scr start")
        start_from = requests.get(API.add_group_url).json()['response']['group_id'] + 1
        group_ids = []
        for group_id in range((start_from), (start_from + self.count)):
            group_ids.append(group_id) 
        groups = self.vk.groups.getById(group_ids = group_ids, fields = 'contacts')
        if not groups:
            groups = self.wait_until_spawn_new_groups()
        
        for group in groups:
            if 'deactivated' in group or 'contacts' not in group or not group['contacts']:
                continue
            else:
                for contact in group['contacts']:
                    try:
                        resp = API.add_group(group['id'], contact['user_id'])
                        if resp['status'] == 'failed':
                            print(resp)
                    except:
                        continue
        

    def run(self):
        while True:
            self.scroll_groups()

gs = GroupScroller()
gs.run()
         