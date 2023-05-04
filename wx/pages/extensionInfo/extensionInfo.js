// walletBalance.js
const app = getApp()

Page({
  data: {
    active: 0,
    page: 1,
    limit: 20,
    isOver: false,
    isLoading: false,
    is_applied: false
  },
  withdrawClick() {
    app.isUserLogin((status) => {
      if (status.status == 1) {
        if (this.data.is_applied) {
          return
        }
        wx.navigateTo({
          url: '/pages/withdraw/withdraw'
        })
      } else {
        wx.navigateTo({
          url: '/pages/login/login',
        })
      }
    })
  },
  onLoad() {
    app.isUserLogin((status) => {
      this.setData({
        loginStatus: status
      })
      if (status.status == 0) {
        wx.navigateTo({
          url: '/pages/login/login',
        })
      } else {
        this.getWalletInfo()
        this.getCheckWithdraw()
        this.setData({
          page: 1,
          isLoading: false,
          isOver: false
        }, () => {
          this.getList()
        })
      }
    })

  },
  // 获取提现状态
  getCheckWithdraw() {
    app.getajax({
      url: "api/user/Center/checkWithdraw",
      success: res => {
        this.setData({
          is_applied: res.data.is_applied
        })
      }
    })
  },
  // 获取列表
  getList() {
    if (this.data.isOver || this.data.isLoading) {
      return
    }
    this.setData({
      isLoading: true
    })
    app.getajax({
      url: 'api/user/distribution/getAchievementList',
      data: {
        page: this.data.page,
        limit: this.data.limit,
        order: 'desc',
        sort: "id"
      },
      success: res => {
        this.setData({
          isLoading: false
        })
        if (this.data.page == 1) {
          this.setData({
            list: res.data.list
          })
        } else {
          this.setData({
            list: [...this.data.list, ...res.data.list]
          })
        }
        if (res.data.list.length < this.data.limit) {
          this.setData({
            isOver: true
          })
        }
        this.setData({
          page: this.data.page + 1
        })
      }
    })
  },
  // 获取钱包信息
  getWalletInfo() {
    app.getajax({
      url: "api/user/center/walletInfo",
      success: res => {
        this.setData({
          walletInfo: res.data
        })
      }
    })
  },
  // 切换列表
  switchList(e) {
    this.setData({
      active: e.currentTarget.dataset.index,
      page: 1,
      isLoading: false,
      isOver: false
    }, () => {
      this.getList()
    })
  },
  onPullDownRefresh() {
    this.setData({
      page: 1,
      isLoading: false,
      isOver: false,
    }, () => {
      this.getList()
    })
  },
  onReachBottom() {
    this.getList()
  }
})