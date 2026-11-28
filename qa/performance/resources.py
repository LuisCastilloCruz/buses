"""Windows resource guards; Python standard library only, no process termination."""
import ctypes,shutil,time
from ctypes import wintypes
class Memory(ctypes.Structure):
 _fields_=[('length',wintypes.DWORD),('load',wintypes.DWORD)]+[(name,ctypes.c_ulonglong) for name in ['total','available','page_total','page_available','virtual_total','virtual_available','extended']]
def cpu_times():
 values=[wintypes.FILETIME() for _ in range(3)]
 if not ctypes.windll.kernel32.GetSystemTimes(*[ctypes.byref(v) for v in values]):raise OSError('CPU metrics unavailable')
 return tuple((v.dwHighDateTime<<32)+v.dwLowDateTime for v in values)
def sample(root,previous=None):
 m=Memory();m.length=ctypes.sizeof(m)
 if not ctypes.windll.kernel32.GlobalMemoryStatusEx(ctypes.byref(m)):raise OSError('Memory metrics unavailable')
 now=cpu_times();cpu=None
 if previous:
  idle=now[0]-previous[0];total=(now[1]-previous[1])+(now[2]-previous[2]);cpu=round(100*(1-idle/total),2) if total else 0
 return {'timestamp':time.time(),'memory_free_mb':round(m.available/1048576,1),'memory_total_mb':round(m.total/1048576,1),'disk_free_gb':round(shutil.disk_usage(root).free/1073741824,2),'cpu_percent':cpu},now
def require_margin(row):
 if row['memory_free_mb']<2048:raise RuntimeError('Resource stop: less than 2 GiB memory free')
 if row['disk_free_gb']<8:raise RuntimeError('Resource stop: less than 8 GiB disk free')
