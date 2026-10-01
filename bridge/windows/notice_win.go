//go:build windows

package main

import (
	"fmt"
	"runtime"
	"sync"
	"syscall"
	"unsafe"
)

const (
	wsPopup        = 0x80000000
	wsCaption      = 0x00C00000
	wsVisible      = 0x10000000
	wsChild        = 0x40000000
	ssCenter       = 0x00000001
	wsExTopmost    = 0x00000008
	wsExToolWin    = 0x00000080
	wsExNoActivate = 0x08000000
	wmClose        = 0x0010
	wmDestroy      = 0x0002
	wmSetFont      = 0x0030
	colorWindow    = 5
	defaultGuiFont = 17
)

var (
	user32Notice   = syscall.NewLazyDLL("user32.dll")
	kernel32Notice = syscall.NewLazyDLL("kernel32.dll")
	gdi32Notice    = syscall.NewLazyDLL("gdi32.dll")

	pRegisterClassExW = user32Notice.NewProc("RegisterClassExW")
	pCreateWindowExW  = user32Notice.NewProc("CreateWindowExW")
	pDestroyWindow    = user32Notice.NewProc("DestroyWindow")
	pDefWindowProcW   = user32Notice.NewProc("DefWindowProcW")
	pGetMessageW      = user32Notice.NewProc("GetMessageW")
	pTranslateMessage = user32Notice.NewProc("TranslateMessage")
	pDispatchMessageW = user32Notice.NewProc("DispatchMessageW")
	pPostQuitMessage  = user32Notice.NewProc("PostQuitMessage")
	pPostMessageW     = user32Notice.NewProc("PostMessageW")
	pShowWindow       = user32Notice.NewProc("ShowWindow")
	pUpdateWindow     = user32Notice.NewProc("UpdateWindow")
	pGetSystemMetrics = user32Notice.NewProc("GetSystemMetrics")
	pSendMessageW     = user32Notice.NewProc("SendMessageW")
	pGetModuleHandleW = kernel32Notice.NewProc("GetModuleHandleW")
	pGetStockObject   = gdi32Notice.NewProc("GetStockObject")
)

type noticeWndClassEx struct {
	CbSize        uint32
	Style         uint32
	LpfnWndProc   uintptr
	CbClsExtra    int32
	CbWndExtra    int32
	HInstance     uintptr
	HIcon         uintptr
	HCursor       uintptr
	HbrBackground uintptr
	LpszMenuName  *uint16
	LpszClassName *uint16
	HIconSm       uintptr
}

type noticePoint struct{ X, Y int32 }
type noticeMsg struct {
	HWnd    uintptr
	Message uint32
	WParam  uintptr
	LParam  uintptr
	Time    uint32
	Pt      noticePoint
}

func noticeWndProc(hwnd uintptr, msg uint32, wParam, lParam uintptr) uintptr {
	switch msg {
	case wmClose:
		pDestroyWindow.Call(hwnd)
		return 0
	case wmDestroy:
		pPostQuitMessage.Call(0)
		return 0
	}
	r, _, _ := pDefWindowProcW.Call(hwnd, uintptr(msg), wParam, lParam)
	return r
}

var noticeWndProcPtr = syscall.NewCallback(noticeWndProc)

// showImportNotice displays a small top-most, non-activating Bridge window.
// It never disables or pauses HairSoft. The returned close function is idempotent.
func showImportNotice(code string) func() {
	ready := make(chan uintptr, 1)
	go func() {
		runtime.LockOSThread()
		defer runtime.UnlockOSThread()
		className, _ := syscall.UTF16PtrFromString("HSVoucherBridgeImportNotice")
		title, _ := syscall.UTF16PtrFromString("HairSoft – online voucher")
		text, _ := syscall.UTF16PtrFromString(fmt.Sprintf("Zapisuji prodaný on-line voucher %s do databáze HairSoft...", code))
		staticClass, _ := syscall.UTF16PtrFromString("STATIC")
		hInst, _, _ := pGetModuleHandleW.Call(0)

		wc := noticeWndClassEx{
			CbSize:        uint32(unsafe.Sizeof(noticeWndClassEx{})),
			LpfnWndProc:   noticeWndProcPtr,
			HInstance:     hInst,
			HbrBackground: uintptr(colorWindow + 1),
			LpszClassName: className,
		}
		pRegisterClassExW.Call(uintptr(unsafe.Pointer(&wc))) // already registered is harmless

		const width, height = 470, 125
		sw, _, _ := pGetSystemMetrics.Call(0)
		sh, _, _ := pGetSystemMetrics.Call(1)
		x := (int(sw) - width) / 2
		y := (int(sh) - height) / 3
		if x < 0 {
			x = 0
		}
		if y < 0 {
			y = 0
		}

		hwnd, _, _ := pCreateWindowExW.Call(
			uintptr(wsExTopmost|wsExToolWin|wsExNoActivate),
			uintptr(unsafe.Pointer(className)),
			uintptr(unsafe.Pointer(title)),
			uintptr(wsPopup|wsCaption|wsVisible),
			uintptr(x), uintptr(y), uintptr(width), uintptr(height),
			0, 0, hInst, 0,
		)
		if hwnd == 0 {
			ready <- 0
			return
		}

		child, _, _ := pCreateWindowExW.Call(
			0,
			uintptr(unsafe.Pointer(staticClass)),
			uintptr(unsafe.Pointer(text)),
			uintptr(wsChild|wsVisible|ssCenter),
			20, 28, width-40, 46,
			hwnd, 0, hInst, 0,
		)
		if child != 0 {
			font, _, _ := pGetStockObject.Call(defaultGuiFont)
			if font != 0 {
				pSendMessageW.Call(child, wmSetFont, font, 1)
			}
		}
		pShowWindow.Call(hwnd, 4) // SW_SHOWNOACTIVATE
		pUpdateWindow.Call(hwnd)
		ready <- hwnd

		var m noticeMsg
		for {
			r, _, _ := pGetMessageW.Call(uintptr(unsafe.Pointer(&m)), 0, 0, 0)
			if int32(r) <= 0 {
				break
			}
			pTranslateMessage.Call(uintptr(unsafe.Pointer(&m)))
			pDispatchMessageW.Call(uintptr(unsafe.Pointer(&m)))
		}
	}()

	hwnd := <-ready
	var once sync.Once
	return func() {
		once.Do(func() {
			if hwnd != 0 {
				pPostMessageW.Call(hwnd, wmClose, 0, 0)
			}
		})
	}
}
