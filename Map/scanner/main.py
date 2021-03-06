import requests
import re
import json
import argparse
import POGOProtos
import POGOProtos.Data_pb2
import POGOProtos.Enums_pb2
import POGOProtos.Inventory_pb2
import POGOProtos.Map_pb2
import POGOProtos.Settings_pb2
import POGOProtos.Networking
import POGOProtos.Networking.Envelopes_pb2
import POGOProtos.Networking.Requests_pb2
import POGOProtos.Networking.Responses_pb2
import POGOProtos.Networking.Requests
import POGOProtos.Networking.Requests.Messages_pb2

from gpsoauth import perform_master_login, perform_oauth
import time

from datetime import datetime
import pymysql
#from geopy.geocoders import GoogleV3
from requests.packages.urllib3.exceptions import InsecureRequestWarning
requests.packages.urllib3.disable_warnings(InsecureRequestWarning)
from s2sphere import *
import codecs


def getNeighbors():
    origin = CellId.from_lat_lng(LatLng.from_degrees(FLOAT_LAT, FLOAT_LNG)).parent(15)

    level = 15
    max_size = 1 << 30
    size = origin.get_size_ij(level)

    face, i, j = origin.to_face_ij_orientation()[0:3]

    walk=  [origin.id(),
            origin.from_face_ij_same(face, i, j - size, j - size >= 0).parent(level).id(),
            origin.from_face_ij_same(face, i, j + size, j + size < max_size).parent(level).id(),
            origin.from_face_ij_same(face, i - size, j, i - size >= 0).parent(level).id(),
            origin.from_face_ij_same(face, i + size, j, i + size < max_size).parent(level).id(),
            origin.from_face_ij_same(face, i - size, j - size, j - size >= 0 and i - size >=0).parent(level).id(),
            origin.from_face_ij_same(face, i + size, j - size, j - size >= 0 and i + size < max_size).parent(level).id(),
            origin.from_face_ij_same(face, i - size, j + size, j + size < max_size and i - size >=0).parent(level).id(),
            origin.from_face_ij_same(face, i + size, j + size, j + size < max_size and i + size < max_size).parent(level).id()]
            #origin.from_face_ij_same(face, i, j - 2*size, j - 2*size >= 0).parent(level).id(),
            #origin.from_face_ij_same(face, i - size, j - 2*size, j - 2*size >= 0 and i - size >=0).parent(level).id(),
            #origin.from_face_ij_same(face, i + size, j - 2*size, j - 2*size >= 0 and i + size < max_size).parent(level).id(),
            #origin.from_face_ij_same(face, i, j + 2*size, j + 2*size < max_size).parent(level).id(),
            #origin.from_face_ij_same(face, i - size, j + 2*size, j + 2*size < max_size and i - size >=0).parent(level).id(),
            #origin.from_face_ij_same(face, i + size, j + 2*size, j + 2*size < max_size and i + size < max_size).parent(level).id(),
            #origin.from_face_ij_same(face, i + 2*size, j, i + 2*size < max_size).parent(level).id(),
            #origin.from_face_ij_same(face, i + 2*size, j - size, j - size >= 0 and i + 2*size < max_size).parent(level).id(),
            #origin.from_face_ij_same(face, i + 2*size, j + size, j + size < max_size and i + 2*size < max_size).parent(level).id(),
            #origin.from_face_ij_same(face, i - 2*size, j, i - 2*size >= 0).parent(level).id(),
            #origin.from_face_ij_same(face, i - 2*size, j - size, j - size >= 0 and i - 2*size >=0).parent(level).id(),
            #origin.from_face_ij_same(face, i - 2*size, j + size, j + size < max_size and i - 2*size >=0).parent(level).id()]

    return walk



API_URL = 'https://pgorelease.nianticlabs.com/plfe/rpc'
LOGIN_URL = 'https://sso.pokemon.com/sso/login?service=https%3A%2F%2Fsso.pokemon.com%2Fsso%2Foauth2.0%2FcallbackAuthorize'
LOGIN_OAUTH = 'https://sso.pokemon.com/sso/oauth2.0/accessToken'

ANDROID_ID = '9774d56d682e549c'
SERVICE= 'audience:server:client_id:848232511240-7so421jotr2609rmqakceuu1luuq0ptb.apps.googleusercontent.com'
APP = 'com.nianticlabs.pokemongo'
CLIENT_SIG = '321187995bc7cdc2b5fc91b11a96e2baa8602c62'

SESSION = requests.session()
SESSION.verify = False
NET_MAXWAIT = 30
LOGIN_MAXWAIT = 5
MAXWAIT = LOGIN_MAXWAIT

FLOAT_LAT = 0
FLOAT_LNG = 0
FLOAT_ALT = 0
EARTH_Rmax = 6378137.0
EARTH_Rmin = 6356752.3
HEX_R = 100.0 #range of detection for pokemon = 100m
HEX_M = 3.0**(0.5)/2.0*HEX_R
LAT_C,LNG_C,ALT_C = [0,0,0]
safety=1.0

HEX_NUM = 0
DATA = []
LOGGING = False
wID = 0

login_type=''
api_endpoint = ''
access_token = ''
response = {}
r = None

LANGUAGE='english' #'german' and 'english' supported

LI_TYPE='ptc' #'google'#
users_ptc= ['#']
passwords_ptc = ['#']

users_google=['#']
passwords_google=['#']

li_user=''
li_password=''

"""def prune_data():
    # prune despawned pokemon
    cur_time = int(time.time())
    for i, poke in reversed(list(enumerate(DATA))):
        if cur_time>poke['despawnTime']:
            DATA.pop(i)"""

"""def write_data_to_file(DATA_FILE):
    try:
        f = open(DATA_FILE, 'w')
        json.dump(DATA, f, indent=2)
    finally:
        f.close()"""

"""def add_pokemon(pokeId, spawnID, lat, lng, despawnT):
    DATA.append({
        'id': pokeId,
        'spawnID': spawnID,
        'lat': lat,
        'lng': lng,
        'despawnTime': despawnT,
    });"""

def getEarthRadius(latrad):
    return (1.0/(((math.cos(latrad))/EARTH_Rmax)**(2) + ((math.sin(latrad))/EARTH_Rmin)**(2)))**(1.0/2)

def set_location_coords(lat, lng, alt):
    global FLOAT_LAT, FLOAT_LNG, FLOAT_ALT
    FLOAT_LAT = lat
    FLOAT_LNG = lng
    FLOAT_ALT = alt

def init_location(LAT_C, LNG_C):
    #print(LAT_C)
    #print(LNG_C)
    latrad=LAT_C*math.pi/180

    a=(HEX_NUM+0.5)
    x_un=1.5*HEX_R/getEarthRadius(latrad)/math.cos(latrad)*safety*a*180/math.pi
    y_un=3.0*HEX_M/getEarthRadius(latrad)*safety*a*180/math.pi
    #xmod=[0,1,2,1,-1,-2,-1]
    #ymod=[0,-1,0,1,1,0,-1]
    lat = LAT_C+ymod[wID]*y_un
    lng = LNG_C+xmod[wID]*x_un
    #lat = LAT_C
    #lng = LNG_C
    set_location_coords(lat,lng,ALT_C)

def login_google(username, password):
    #print('[!] Google login for: {}'.format(username))
    r1 = perform_master_login(username, password, ANDROID_ID)
    r2 = perform_oauth(username, r1.get('Token', ''), ANDROID_ID, SERVICE, APP,
        CLIENT_SIG)
    return r2.get('Auth') # access token

def login_ptc(username, password):
    global access_token
    global login_type
    login_type='ptc'
    r = None
    while True:
        try:
            #SESSION.headers.update({'User-Agent': 'Niantic App'})
            SESSION.headers.update({'User-Agent': 'niantic'})
            r = SESSION.get(LOGIN_URL)
            retry_after=1
            while r.status_code!=200:
                #print('[-] Connection error {}, retrying in {} seconds (step 1)'.format(r.status_code,retry_after))
                return
                time.sleep(retry_after)
                retry_after=min(retry_after*2,MAXWAIT)
                r = SESSION.get(LOGIN_URL)


            jdata = json.loads(r.content.decode())
            data = {
                'lt': jdata['lt'],
                'execution': jdata['execution'],
                '_eventId': 'submit',
                'username': username,
                'password': password,
            }
            r = SESSION.post(LOGIN_URL, data=data)
            retry_after=1

            while r.status_code!=500 and r.status_code!=200:
                print('[-] Connection error {}, retrying in {} seconds (step 2)'.format(r.status_code,retry_after))
                return
                time.sleep(retry_after)
                retry_after=min(retry_after*2,MAXWAIT)
                r = SESSION.post(LOGIN_URL, data=data)

            ticket = None
            ticket = re.sub('.*ticket=', '', r.history[0].headers['Location'])
            data1 = {
                'client_id': 'mobile-app_pokemon-go',
                'redirect_uri': 'https://www.nianticlabs.com/pokemongo/error',
                'client_secret': 'w8ScCUXJQc6kXKw8FiOhd8Fixzht18Dq3PEVkUCP5ZPxtgyWsbTvWHFLm2wNY0JR',
                'grant_type': 'refresh_token',
                'code': ticket,
            }
            r = SESSION.post(LOGIN_OAUTH, data=data1)
            while r.status_code!=200:
                print('[-] Connection error {}, retrying in {} seconds (step 3)'.format(r.status_code,retry_after))
                return
                time.sleep(retry_after)
                retry_after=min(retry_after*2,MAXWAIT)
                r = SESSION.post(LOGIN_OAUTH, data=data1)

            access_token = re.sub('&expires.*', '', r.content.decode())
            access_token = re.sub('.*access_token=', '', access_token)
            return

        except Exception as e:
            print('[-] Uncaught connection error, error: {}'.format(e))
            if r is not None:
                print('[-] Uncaught connection error, http code: {}'.format(r.status_code))
            else:
                print('[-] Error happened before network request.')
            print('[-] Retrying...')
            time.sleep(2)


def do_login():
    if LI_TYPE=='ptc':
        li_user = users_ptc[wID]
        li_password = passwords_ptc[wID]
        #print('[+] login for ptc account: {}'.format(li_user))
        login_ptc(li_user,li_password)
    elif LI_TYPE=='google':
        li_user = users_google[wID]
        li_password = passwords_google[wID]
        #print('[+] login for google account: {}'.format(li_user))
        login_google(li_user,li_password)
    else:
        raise Exception('login type should be either ptc or google')

def api_req(api_endpoint, access_token, *mehs, **kw):
    r=None;
    while True:
        try:
            p_req = POGOProtos.Networking.Envelopes_pb2.RequestEnvelope()
            p_req.request_id = 1469378659230941192

            p_req.status_code = POGOProtos.Networking.Envelopes_pb2.GET_PLAYER

            p_req.latitude, p_req.longitude, p_req.altitude = (FLOAT_LAT, FLOAT_LNG, FLOAT_ALT)

            p_req.unknown12 = 989 #transaction id, anything works
            if 'useauth' not in kw or not kw['useauth']:
                p_req.auth_info.provider = login_type
                p_req.auth_info.token.contents = access_token
                p_req.auth_info.token.unknown2 = 14
            else:
                p_req.auth_ticket.start = kw['useauth'].start
                p_req.auth_ticket.expire_timestamp_ms = kw['useauth'].expire_timestamp_ms
                p_req.auth_ticket.end = kw['useauth'].end

            for meh in mehs:
                p_req.MergeFrom(meh)
            protobuf = p_req.SerializeToString()
            r = SESSION.post(api_endpoint, data=protobuf, verify=False)

            retry_after=1
            while r.status_code!=200:
                print('[-] Connection error {}, retrying in {} seconds'.format(r.status_code,retry_after))
                time.sleep(retry_after)
                retry_after=min(retry_after*2,MAXWAIT)
                r = SESSION.post(api_endpoint, data=protobuf, verify=False)

            p_ret = POGOProtos.Networking.Envelopes_pb2.ResponseEnvelope()
            p_ret.ParseFromString(r.content)
            return p_ret

        except Exception as e:
            #print('[-] Uncaught connection error, error: {}'.format(e))
            if r is not None:
                print('[-] Uncaught connection error, http code: {}'.format(r.status_code))
            else:
                print('[-] Error happened before network request.')
            print('[-] Retrying...')
            time.sleep(2)

def get_profile(access_token, api, useauth, *reqq):
    req = POGOProtos.Networking.Envelopes_pb2.RequestEnvelope()

    req1 = req.requests.add()
    req1.request_type = POGOProtos.Networking.Envelopes_pb2.GET_PLAYER
    if len(reqq) >= 1:
        req1.MergeFrom(reqq[0])

    req2 = req.requests.add()
    req2.request_type = POGOProtos.Networking.Envelopes_pb2.GET_HATCHED_EGGS
    if len(reqq) >= 2:
        req2.MergeFrom(reqq[1])

    req3 = req.requests.add()
    req3.request_type = POGOProtos.Networking.Envelopes_pb2.GET_INVENTORY
    if len(reqq) >= 3:
        req3.MergeFrom(reqq[2])

    req4 = req.requests.add()
    req4.request_type = POGOProtos.Networking.Envelopes_pb2.CHECK_AWARDED_BADGES
    if len(reqq) >= 4:
        req4.MergeFrom(reqq[3])

    req5 = req.requests.add()
    req5.request_type = POGOProtos.Networking.Envelopes_pb2.DOWNLOAD_SETTINGS
    if len(reqq) >= 5:
        req5.MergeFrom(reqq[4])

    newResponse = api_req(api, access_token, req, useauth = useauth)

    retry_after=1
    while (newResponse.status_code not in [1,2,53,52,102]): #1 for hearbeat, 2 for profile authorization, 53 for api endpoint, 52 for error, 102 session token invalid
        print('[-] Response error, status code: {}, retrying in {} seconds'.format(newResponse.status_code,retry_after))
        time.sleep(retry_after)
        retry_after=min(retry_after*2,MAXWAIT)
        newResponse = api_req(api, access_token, req, useauth = useauth)

    return newResponse

def set_api_endpoint():
    global api_endpoint
    p_ret = get_profile(access_token, API_URL, None)
    while p_ret.status_code==102:
        print('[-] Error, invalid session, retrying...')
        #time.sleep(300) #at that point the severs are pretty much done for, so waiting for 5 min
        sys.exit()
        p_ret = get_profile(access_token, API_URL, None)

    api_endpoint = ('https://%s/rpc' % p_ret.api_url)

def authorize_profile():
    global response
    response = get_profile(access_token, api_endpoint, None)

def heartbeat():
    while True:
        lastR_t = int(time.time()*1000)
        m5 = POGOProtos.Networking.Envelopes_pb2.RequestEnvelope().requests.add()
        #m5.request_type = POGOProtos.Networking.Envelopes_pb2.DOWNLOAD_SETTINGS
        m51 = POGOProtos.Networking.Requests.Messages_pb2.DownloadSettingsMessage()
        m51.hash = "05daf51635c82611d1aac95c0b051d3ec088a930"
        m5.request_message = m51.SerializeToString()

        m4 = POGOProtos.Networking.Envelopes_pb2.RequestEnvelope().requests.add()
        #m4.request_type = POGOProtos.Networking.Envelopes_pb2.CHECK_AWARDED_BADGES
        #m41 = POGOProtos.Networking.Requests.Messages_pb2.CheckAwardedBadgesMessage()


        m3 = POGOProtos.Networking.Envelopes_pb2.RequestEnvelope().requests.add()
        #m3.request_type = POGOProtos.Networking.Envelopes_pb2.GET_INVENTORY
        m31 = POGOProtos.Networking.Requests.Messages_pb2.GetInventoryMessage()
        m31.last_timestamp_ms = lastR_t
        m3.request_message = m31.SerializeToString()


        m2 = POGOProtos.Networking.Envelopes_pb2.RequestEnvelope().requests.add()
        #m2.request_type = POGOProtos.Networking.Envelopes_pb2.GET_HATCHED_EGGS
        #m21 = POGOProtos.Networking.Requests.Messages_pb2.GetHatchedEggsMessage()


        m1 = POGOProtos.Networking.Envelopes_pb2.RequestEnvelope().requests.add()
        m1.request_type = POGOProtos.Networking.Envelopes_pb2.GET_MAP_OBJECTS
        m11 = POGOProtos.Networking.Requests.Messages_pb2.GetMapObjectsMessage()

        walk = sorted(getNeighbors())
        m11.cell_id.extend(walk)

        m11.since_timestamp_ms.extend([0]*len(walk))
        m11.latitude = FLOAT_LAT
        m11.longitude = FLOAT_LNG
        m1.request_message = m11.SerializeToString()
        newResponse = get_profile(access_token, api_endpoint, response.auth_ticket, m1, m2, m3, m4, m5)
        if newResponse.status_code==1:
            payload = newResponse.returns[0]
            heartbeat = POGOProtos.Networking.Responses_pb2.GetMapObjectsResponse()
            heartbeat.ParseFromString(payload)
            return heartbeat
        elif newResponse.status_code==2:
            authorize_profile()
        elif newResponse.status_code==102:
            #print('[-] Error, refreshing login')
            do_login()
            set_api_endpoint()
            authorize_profile()
        else:
            #print('[-] Heartbeat error, status code: {}'.format(newResponse.status_code)) #should not happen, probably unused
            #print('[-] Retrying...')
            time.sleep(2)

def fixNum(int_type):
    length = 0
    int_ret = ~int_type
    while (int_ret):
        int_ret >>= 1
        length += 1
    int_ret = int_type ^ (-1 << length)
    int_ret = int_ret-0x1A000
    return int_ret

def main():
    global wID
    global li_user
    global li_password
    global HEX_NUM
    global MAXWAIT
    interval=0
    parser = argparse.ArgumentParser()
    parser.add_argument("-id", "--id", help="worker id")
    #parser.add_argument("-lat", "--latitude", help="latitude coordinates")
    #parser.add_argument("-lng", "--longitude", help="longitude coordinates")
    parser.add_argument("-c", "--coords", help="coordinates")
    parser.add_argument("-r", "--range", help="scan range")
    parser.add_argument("-t", "--timeinterval", help="time interval")
    parser.set_defaults(id=0,range=20,timeinterval=300)

    args = parser.parse_args()

    if args.id is not None:
        wID=int(args.id)
        if wID < 0 or wID > 6:
            raise TypeError('id must be positive')

    if args.range is not None:
        HEX_NUM=int(args.range)
        if HEX_NUM < 0 or HEX_NUM > 100:
            raise TypeError('range must be between 0 and 100, recommended: 20')
    #if args.latitude is not None:
   # 	LAT_C = float(args.latitude)
   # 	print(args.latitude)
   # if args.longitude is not None:
   # 	LNG_C = float(args.longitude)
    if args.coords is not None:
   	    splitted = args.coords.split(',')
   	    LAT_C = float(splitted[0])
   	    LNG_C = float(splitted[1])
   	    #print(LAT_C)
   	    #print(LNG_C)

    if args.timeinterval is not None:
        interval=int(args.timeinterval)
        if interval < 0 or interval > 900:
            raise TypeError('time interval must be between 0 and 900, recommended 600')

    DATA_FILE = 'res/data{}.json'.format(wID)
    STAT_FILE = 'res/spawns{}.json'.format(wID)

    pokemons_json = codecs.open('scanner/res/'+LANGUAGE+'.json', 'r', 'utf-8-sig')
    #print (pokemons_json)
    pokemons = json.load(pokemons_json)
    init_location(LAT_C, LNG_C)

    do_login()

    #print('[+] RPC Session Token: {}'.format(access_token))

    set_api_endpoint()
    #print('[+] API endpoint: {}'.format(api_endpoint))
    authorize_profile()
    #print('[+] Login successful')

    payload = response.returns[0]
    profile = POGOProtos.Networking.Responses_pb2.GetPlayerResponse()
    profile.ParseFromString(payload)

    #print('[+] Username: {}'.format(profile.player_data.username))

    creation_time = datetime.fromtimestamp(int(profile.player_data.creation_timestamp_ms)/1000)
    #print('[+] You are playing Pokemon Go since: {}'.format(creation_time.strftime('%Y-%m-%d %H:%M:%S')))
    #for curr in profile.player_data.currencies:
        #print('[+] {}: {}'.format(curr.name, curr.amount))

    MAXWAIT=NET_MAXWAIT
    origin = LatLng.from_degrees(FLOAT_LAT, FLOAT_LNG)
    all_ll = [origin]
    maxR = 1;
    for a in range(1,HEX_NUM+1):
        for i in range(0,a*6):
            latrad = origin.lat().radians

            x_un=1.5*HEX_R/getEarthRadius(latrad)/math.cos(latrad)*safety*180/math.pi
            y_un=1.0*HEX_M/getEarthRadius(latrad)*safety*180/math.pi
            if i < a:
                lat = FLOAT_LAT+y_un*(-2*a+i)
                lng = FLOAT_LNG+x_un*i
            elif i < 2*a:
                lat = FLOAT_LAT+y_un*(-3*a+2*i)
                lng = FLOAT_LNG+x_un*a
            elif i < 3*a:
                lat = FLOAT_LAT+y_un*(-a+i)
                lng = FLOAT_LNG+x_un*(3*a-i)
            elif i < 4*a:
                lat = FLOAT_LAT+y_un*(5*a-i)
                lng = FLOAT_LNG+x_un*(3*a-i)
            elif i < 5*a:
                lat = FLOAT_LAT+y_un*(9*a-2*i)
                lng = FLOAT_LNG+x_un*-a
            else:
                lat = FLOAT_LAT+y_un*(4*a-i)
                lng = FLOAT_LNG+x_un*(-6*a+i)

            all_ll.append(LatLng.from_degrees(lat,lng))
            maxR+=1;

    #/////////////////

    #////////////////////
    #print('')
    #try:
        #f=open(STAT_FILE,'a')
        #if f.tell()==0:
            #f.write('Name\tid\tSpawnID\tlat\tlng\tspawnTime\tTime\tTime2Hidden\tencounterID\n')
    #finally:
        #f.close()
    seen = set([])
    uniqueE = set([])

    conn = pymysql.connect(host='#',  user='#', passwd='#', db='#')

    for i in range(1):
        curT=int(time.time())
        curR=0;
        #print("[+] Time: " + datetime.now().strftime("%H:%M:%S"))
        for this_ll in all_ll:
            #if LOGGING:
                #print('[+] Finished: '+str(100.0*curR/maxR)+' %')
            curR+=1
            set_location_coords(this_ll.lat().degrees, this_ll.lng().degrees, ALT_C)
            h = heartbeat()
            try:
                #f= open(STAT_FILE,mode='a',buffering=1)
                for cell in h.map_cells:
                    for wild in cell.wild_pokemons:
                        if (wild.encounter_id not in seen):
                            seen.add(wild.encounter_id)
                            if (wild.encounter_id not in uniqueE):
                                spawnIDint=int(wild.spawn_point_id, 16)
                                org_tth=wild.time_till_hidden_ms
                                if wild.time_till_hidden_ms < 0:
                                    wild.time_till_hidden_ms=901000
                                else:
                                    uniqueE.add(wild.encounter_id)
                                now = datetime.now()
                                #if (wild.pokemon_data.pokemon_id != None):
                                #print(wild.pokemon_data.pokemon_id)
                                buf = "CALL sp_insertIntoPokemonInfo(\'%s\',%s,%s,%s,%s,\'%s\',\'%s\', %s)" % ( pokemons[wild.pokemon_data.pokemon_id], wild.latitude, wild.longitude, now.hour, now.minute, now, now, int(wild.time_till_hidden_ms / 1000))
                                cur = conn.cursor()
                                cur.execute(buf)
                                conn.commit()
                                cur.close() 
                                #f.write('{}\t{}\t{}\t{}\t{}\t{}\t{}\t{}\t{}\n'.format(pokemons[wild.pokemon_data.pokemon_id],wild.pokemon_data.pokemon_id,spawnIDint,wild.latitude,wild.longitude,(wild.last_modified_timestamp_ms+wild.time_till_hidden_ms)/1000.0-900.0,wild.last_modified_timestamp_ms/1000.0,org_tth/1000.0,wild.encounter_id))
                                #add_pokemon(wild.pokemon_data.pokemon_id,spawnIDint, wild.latitude, wild.longitude, int((wild.last_modified_timestamp_ms+wild.time_till_hidden_ms)/1000.0))

                                if LOGGING:
                                    other = LatLng.from_degrees(wild.latitude, wild.longitude)
                                    diff = other - origin
                                    difflat = diff.lat().degrees
                                    difflng = diff.lng().degrees
                                    direction = (('N' if difflat >= 0 else 'S') if abs(difflat) > 1e-4 else '')  + (('E' if difflng >= 0 else 'W') if abs(difflng) > 1e-4 else '')
                                    #print("<<>> (%s) %s visible for %s seconds (%sm %s from you)" % (wild.pokemon_data.pokemon_id, pokemons[wild.pokemon_data.pokemon_id], int(wild.time_till_hidden_ms/1000.0), int(origin.get_distance(other).radians * 6366468.241830914), direction))
            finally:
                #f.close()
                #conn.close()
                #print('')
                temp = 1
		
            #write_data_to_file(DATA_FILE)
            if LOGGING:
                #print('')
                temp = 1
            #time.sleep(1)
        uniqueE=uniqueE & seen
        seen.clear()
        curT = int(time.time())-curT
        #print('[+] Scan Time: {} s'.format(curT))
        curT=max(interval-curT,0)
        #print('[+] Sleeping for {} seconds...'.format(curT))
        time.sleep(curT)
       # prune_data()
    conn.close()
if __name__ == '__main__':

    main()
