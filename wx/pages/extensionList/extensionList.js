// pages/extensionList/extensionList.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    index: 0,
    distribtionInfo: {},
    type: 0,
    array: ['加入时间排序', '交易金额排序']
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    this.getMyDistribtionInfo()
    this.getList()
  },
  // 获取分销列表
  getList() {
    app.getajax({
      url: "api/user/distribution/getDistributionList",
      data: {
        type: ~~this.data.index + 1,
        limit: 999,
      },
      success: res => {
        wx.stopPullDownRefresh({})
        console.log(res.data.list)
        this.setData({
          list: res.data.list,
          invalid: res.data.invalid,
          valid: res.data.valid,
          total:res.data.total
        })
      }
    })
  },
  // 获取我的分销
  getMyDistribtionInfo() {
    app.getajax({
      url: "api/user/distribution/getDistributionInfo",
      success: res => {
        this.setData({
          distribtionInfo: res.data
        })
      }
    })
  },
  bindPickerChange: function (e) {
    console.log('picker发送选择改变，携带值为', e.detail.value)
    this.setData({
      type: e.detail.value
    }, () => {
      this.getList()
    })
  },
  switchTop(e) {
    this.setData({
      index: e.currentTarget.dataset.index
    }, () => {
      this.getList()
    })
  },
  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {
    this.getList()
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})