//go:build windows

package main

import (
	"errors"
	"syscall"
	"unsafe"
)

var (
	kernel32Singleton = syscall.NewLazyDLL("kernel32.dll")
	pCreateMutexW     = kernel32Singleton.NewProc("CreateMutexW")
	pCloseHandle      = kernel32Singleton.NewProc("CloseHandle")
)

const errorAlreadyExists syscall.Errno = 183

func acquireSingleton() (func(), error) {
	name, _ := syscall.UTF16PtrFromString("UnisSoft.HSVoucherBridge.Singleton")
	h, _, e := pCreateMutexW.Call(0, 0, uintptr(unsafe.Pointer(name)))
	if h == 0 {
		if e != nil && !errors.Is(e, syscall.Errno(0)) {
			return nil, e
		}
		return nil, syscall.EINVAL
	}
	if errno, ok := e.(syscall.Errno); ok && errno == errorAlreadyExists {
		pCloseHandle.Call(h)
		return nil, errorAlreadyExists
	}
	return func() { pCloseHandle.Call(h) }, nil
}
