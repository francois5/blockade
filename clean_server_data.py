#!/usr/bin/env python3

import sqlite3

conn = sqlite3.connect('db/blockade.db')
cur = conn.cursor()
cur.execute('''DELETE FROM games''')
conn.commit()
conn.close()

open('db/wait_player.id', 'w').close()
open('debug.log', 'w').close()
open('debug_end.log', 'w').close()
